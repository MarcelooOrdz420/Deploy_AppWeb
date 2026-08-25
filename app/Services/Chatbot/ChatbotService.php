<?php

namespace App\Services\Chatbot;

use App\Models\MarketingOffer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public function __construct(
        private readonly OpenAIResponsesClient $openai,
        private readonly OllamaClient $ollama,
        private readonly LocalResponder $local,
    ) {
    }

    public function reply(string $message, ?User $user = null, ?string $sessionId = null, ?string $draftContext = null): string
    {
        $blocked = $this->blockedSensitiveReply($message);
        if ($blocked) {
            return $blocked;
        }

        $system = $this->buildSystemPrompt($user, $sessionId, $draftContext);
        [$provider, $model] = $this->resolveProviderAndModel();
        $local = $this->local->reply($message);

        if ($local && $this->shouldPreferLocal($message, $provider)) {
            return $local;
        }

        try {
            $text = match ($provider) {
                'local' => $local,
                'ollama' => $this->ollama->respond($model, $system, $message),
                default => $this->openai->respond($model, $system, $message),
            };

            $text = preg_replace("/\\s+$/", '', (string) $text);

            return trim($text) !== '' ? trim($text) : $this->fallback();
        } catch (\Throwable $e) {
            Log::warning('Chatbot LLM failed', [
                'error' => $e->getMessage(),
                'provider' => $provider,
                'model' => $model,
                'session_id' => $sessionId,
                'user_id' => auth()->id(),
            ]);

            return $local ?: $this->fallback();
        }
    }

    public function status(): array
    {
        [$provider, $model] = $this->resolveProviderAndModel();

        if ($provider === 'local') {
            return [
                'provider' => $provider,
                'ok' => true,
                'model' => $model,
                'message' => 'POLL-IA esta usando respuestas locales sin credenciales externas.',
            ];
        }

        if ($provider === 'ollama') {
            return [
                'provider' => $provider,
                ...$this->ollama->status($model),
            ];
        }

        return [
            'provider' => $provider,
            'ok' => trim((string) config('chatbot.openai.api_key')) !== '',
            'model' => $model,
            'message' => trim((string) config('chatbot.openai.api_key')) !== ''
                ? 'OpenAI esta configurado.'
                : 'OPENAI_API_KEY no esta configurado.',
        ];
    }

    private function buildSystemPrompt(?User $user, ?string $sessionId, ?string $draftContext = null): string
    {
        $brand = (string) config('chatbot.brand_name');
        $supportPhone = (string) config('chatbot.support_phone');
        $supportEmail = (string) config('chatbot.support_email');
        $hours = (string) config('chatbot.hours');
        $knowledge = $this->readKnowledge();
        $payments = $this->paymentContext();
        $products = $this->productsContext();
        $promotions = $this->promotionsContext();
        $orders = $this->ordersContext($user);

        return trim(implode("\n", array_filter([
            "Eres POLL-IA, el asistente oficial de {$brand}.",
            'Responde en espanol, con tono amable, profesional y directo.',
            'Solo respondes sobre temas publicos del negocio: productos, precios, promociones y descuentos vigentes, pedidos, pagos, delivery, horarios, ubicacion, contacto, y como usar la app/web. Tambien puedes responder preguntas generales sobre a que se dedica el negocio (que vende, como funciona) usando solo la informacion publica de este contexto.',
            $orders
                ? "El cliente esta logueado (correo: {$user?->email}) y estos son sus pedidos recientes:\n{$orders}\nUsa estos datos directamente para responder sobre el estado de su pedido. NO le pidas codigo de tracking ni correo: ya los tienes. Empieza esa parte de tu respuesta con una linea '## Pedido <codigo de tracking>' y debajo el estado, el pago y el total, cada dato en su propia linea empezando con '- '."
                : 'Si falta informacion para revisar un pedido, pide 1 o 2 datos concretos, por ejemplo codigo de tracking o correo.',
            'Si hay un pedido temporal en contexto, no vuelvas a preguntar esos mismos productos o datos. Solo pide lo que falte.',
            'Si el usuario pide algo fuera del negocio, responde que no aplica y ofrece el contacto humano.',
            'No inventes precios, disponibilidad, horarios ni datos de pago: usa el contexto disponible.',
            'Nunca muestres cantidades de stock ni existencias exactas. Si un producto esta en el catalogo publico, solo puedes decir que esta disponible.',
            'No reveles ni solicites datos internos, administrativos, credenciales, tokens, claves, contrasenas, reportes internos, datos de clientes, direcciones privadas, DNI/RUC de clientes, correos privados ni configuracion del sistema.',
            'Para consultas de productos usa solo el catalogo publico incluido en este contexto. Usa SIEMPRE este formato exacto: por cada categoria, una linea que empiece con "## " seguida del nombre de la categoria, y debajo cada producto en su propia linea empezando con "- ", con el nombre y el precio (ejemplo: "- Pollo entero - S/ 45.00"). No mezcles productos de categorias distintas en el mismo bloque.',
            'No uses markdown de negrita ni asteriscos (nunca **texto**), ni emojis. Las unicas marcas que puedes usar son "## " para un titulo de seccion y "- " para un dato o producto en una lista, siempre al inicio de la linea.',
            "Horario: {$hours}.",
            "Soporte: {$supportPhone} / {$supportEmail}.",
            $payments ? "Medios de pago y datos utiles:\n{$payments}" : null,
            $products ? "Productos disponibles de referencia:\n{$products}" : null,
            $promotions ? "Promociones activas ahora mismo (cada una ya indica si aplica solo a compras web/app o tambien presenciales, respeta ese dato al responder):\n{$promotions}" : 'No hay promociones activas ahora mismo. Si te preguntan, dilo con naturalidad y anima a revisar el inicio de la app o la web.',
            $draftContext ? "Pedido temporal ya indicado por el cliente:\n{$draftContext}" : null,
            $knowledge ? "Base de conocimiento:\n{$knowledge}" : null,
        ])));
    }

    private function ordersContext(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        try {
            $orders = Order::query()
                ->where('user_id', $user->id)
                ->latest()
                ->limit(3)
                ->get(['tracking_code', 'status', 'payment_status', 'total_amount', 'created_at']);
        } catch (\Throwable) {
            return null;
        }

        if ($orders->isEmpty()) {
            return null;
        }

        return $orders->map(function (Order $order): string {
            $status = Order::statusLabel((string) $order->status);
            $date = optional($order->created_at)->format('d/m/Y H:i');

            return "- Pedido {$order->tracking_code}: estado {$status}, pago {$order->payment_status}, total S/ "
                .number_format((float) $order->total_amount, 2, '.', '')
                ." ({$date})";
        })->implode("\n");
    }

    private function resolveProviderAndModel(): array
    {
        $provider = strtolower(trim((string) config('chatbot.provider', 'ollama')));

        if ((bool) config('chatbot.ollama.enabled', false)) {
            return ['ollama', (string) config('chatbot.ollama.model', 'llama3.1:8b')];
        }

        if ($provider === 'local') {
            return ['local', 'knowledge-base'];
        }

        if ($provider === 'openai') {
            return ['openai', (string) config('chatbot.openai.model', 'gpt-4.1-mini')];
        }

        return ['ollama', (string) config('chatbot.ollama.model', 'llama3.1:8b')];
    }

    private function readKnowledge(): ?string
    {
        $path = (string) config('chatbot.knowledge_path');
        if ($path === '' || ! is_file($path)) {
            return null;
        }

        $content = @file_get_contents($path);
        $content = is_string($content) ? trim($content) : '';

        return $content !== '' ? $content : null;
    }

    private function paymentContext(): ?string
    {
        $payments = (array) config('company.payments', []);
        $lines = [];

        if (($payments['izipay']['enabled'] ?? false)) {
            $label = trim((string) ($payments['izipay']['label'] ?? 'Pago con tarjeta'));
            $message = trim((string) ($payments['izipay']['message'] ?? 'Paga con tarjeta desde el checkout seguro.'));
            $lines[] = "- {$label}: {$message}";
        }

        if (($payments['cod']['enabled'] ?? false)) {
            $label = trim((string) ($payments['cod']['label'] ?? 'Pago contraentrega'));
            $message = trim((string) ($payments['cod']['message'] ?? 'Paga al recibir tu pedido en el lugar acordado.'));
            $lines[] = "- {$label}: {$message}";
        }

        return $lines ? implode("\n", $lines) : null;
    }

    private function productsContext(): ?string
    {
        try {
            $products = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->orderBy('category')
                ->orderBy('name')
                ->limit(30)
                ->get(['name', 'price', 'category', 'description']);
        } catch (\Throwable) {
            return null;
        }

        if ($products->isEmpty()) {
            return null;
        }

        return $products->map(function (Product $product): string {
            $category = trim((string) $product->category);
            $categoryText = $category !== '' ? $category : 'general';
            $description = trim((string) $product->description);
            $descriptionText = $description !== '' ? " | {$description}" : '';

            return "- {$product->name} | Categoria: {$categoryText} | Precio: S/ "
                .number_format((float) $product->price, 2, '.', '')
                ." | Disponibilidad: disponible{$descriptionText}";
        })->implode("\n");
    }

    private function promotionsContext(): ?string
    {
        try {
            $offers = MarketingOffer::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->with('product:id,name')
                ->orderByRaw('ends_at IS NULL, ends_at ASC')
                ->limit(3)
                ->get();
        } catch (\Throwable) {
            return null;
        }

        if ($offers->isEmpty()) {
            return null;
        }

        return $offers->map(function (MarketingOffer $offer): string {
            $product = $offer->product?->name ?? 'un platillo';
            $scope = $offer->online_only ? 'solo compras web/app' : 'tambien compras presenciales';

            return "- {$offer->title} ({$product}): antes S/ ".number_format((float) $offer->original_price, 2, '.', '')
                .', ahora S/ '.number_format((float) $offer->promo_price, 2, '.', '')
                ." (-{$offer->discount_percent}%, {$scope})";
        })->implode("\n");
    }

    private function shouldPreferLocal(string $message, string $provider): bool
    {
        if ($provider === 'local') {
            return true;
        }

        $normalized = str((string) $message)->lower()->ascii()->toString();

        foreach (['producto', 'productos', 'menu', 'carta', 'pollos', 'parrillas', 'bebidas', 'precio', 'precios', 'stock', 'disponible', 'disponibles', 'barato', 'economico', 'combo', 'combinar', 'recomienda', 'promocion', 'promociones', 'oferta', 'ofertas', 'descuento', 'descuentos'] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function blockedSensitiveReply(string $message): ?string
    {
        $normalized = str((string) $message)->lower()->ascii()->toString();

        $blocked = [
            'admin',
            'administrador',
            'credencial',
            'credenciales',
            'password',
            'contrasena',
            'clave',
            'secret',
            'token',
            'api key',
            'apikey',
            'base de datos',
            'database',
            'db_password',
            'pusher secret',
            'openai_api_key',
            'resend_api_key',
            'usuarios',
            'clientes registrados',
            'correos de clientes',
            'telefonos de clientes',
            'dni de clientes',
            'direcciones de clientes',
            'reporte interno',
            'ventas internas',
            'cierre de caja',
        ];

        foreach ($blocked as $needle) {
            if (str_contains($normalized, $needle)) {
                return 'No puedo mostrar datos internos, administrativos, credenciales ni informacion privada de clientes. Si necesitas ayuda, puedo orientarte con productos publicos, precios disponibles, pagos, delivery, horarios, ubicacion o seguimiento de tu propio pedido.';
            }
        }

        return null;
    }

    private function fallback(): string
    {
        $brand = (string) config('chatbot.brand_name');
        $supportPhone = (string) config('chatbot.support_phone');
        $supportEmail = (string) config('chatbot.support_email');

        return "Ahora mismo no puedo responder con el asistente IA. Para ayudarte mas rapido, escribenos a {$supportPhone} o {$supportEmail} ({$brand}).";
    }
}
