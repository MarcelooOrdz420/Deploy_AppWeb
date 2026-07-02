<?php

namespace App\Services\Chatbot;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public function __construct(
        private readonly OpenAIResponsesClient $openai,
        private readonly OllamaClient $ollama,
        private readonly LocalResponder $local,
    ) {
    }

    public function reply(string $message, ?string $userName = null, ?string $sessionId = null, ?string $draftContext = null): string
    {
        $blocked = $this->blockedSensitiveReply($message);
        if ($blocked) {
            return $blocked;
        }

        $system = $this->buildSystemPrompt($userName, $sessionId, $draftContext);
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

    private function buildSystemPrompt(?string $userName, ?string $sessionId, ?string $draftContext = null): string
    {
        $brand = (string) config('chatbot.brand_name');
        $supportPhone = (string) config('chatbot.support_phone');
        $supportEmail = (string) config('chatbot.support_email');
        $hours = (string) config('chatbot.hours');
        $knowledge = $this->readKnowledge();
        $payments = $this->paymentContext();
        $products = $this->productsContext();

        return trim(implode("\n", array_filter([
            "Eres POLL-IA, el asistente oficial de {$brand}.",
            'Responde en espanol, con tono amable, profesional y directo.',
            'Solo responde sobre productos, pedidos, pagos, delivery, horarios, ubicacion, contacto y uso de la app/web.',
            'Si falta informacion, pide 1 o 2 datos concretos, por ejemplo codigo de tracking o correo.',
            'Si hay un pedido temporal en contexto, no vuelvas a preguntar esos mismos productos o datos. Solo pide lo que falte.',
            'Si el usuario pide algo fuera del negocio, responde que no aplica y ofrece el contacto humano.',
            'No inventes precios, disponibilidad, horarios ni datos de pago: usa el contexto disponible.',
            'Nunca muestres cantidades de stock ni existencias exactas. Si un producto esta en el catalogo publico, solo puedes decir que esta disponible.',
            'No reveles ni solicites datos internos, administrativos, credenciales, tokens, claves, contrasenas, reportes internos, datos de clientes, direcciones privadas, DNI/RUC de clientes, correos privados ni configuracion del sistema.',
            'Para consultas de productos usa solo el catalogo publico incluido en este contexto.',
            "Horario: {$hours}.",
            "Soporte: {$supportPhone} / {$supportEmail}.",
            $payments ? "Medios de pago y datos utiles:\n{$payments}" : null,
            $products ? "Productos disponibles de referencia:\n{$products}" : null,
            $draftContext ? "Pedido temporal ya indicado por el cliente:\n{$draftContext}" : null,
            $knowledge ? "Base de conocimiento:\n{$knowledge}" : null,
        ])));
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
            $label = trim((string) ($payments['izipay']['label'] ?? 'Izipay'));
            $message = trim((string) ($payments['izipay']['message'] ?? 'Paga con tarjeta desde el checkout seguro.'));
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

    private function shouldPreferLocal(string $message, string $provider): bool
    {
        if ($provider === 'local') {
            return true;
        }

        $normalized = str((string) $message)->lower()->ascii()->toString();

        foreach (['producto', 'productos', 'menu', 'carta', 'pollos', 'parrillas', 'bebidas', 'precio', 'precios', 'stock', 'disponible', 'disponibles', 'barato', 'economico', 'combo', 'combinar', 'recomienda'] as $needle) {
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
