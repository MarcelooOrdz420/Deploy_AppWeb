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
    )
    {
    }

    public function reply(string $message, ?string $userName = null, ?string $sessionId = null): string
    {
        $system = $this->buildSystemPrompt($userName, $sessionId);
        [$provider, $model] = $this->resolveProviderAndModel();

        try {
            $text = match ($provider) {
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
            $local = $this->local->reply($message);
            return $local ?: $this->fallback();
        }
    }

    private function buildSystemPrompt(?string $userName, ?string $sessionId): string
    {
        $brand = (string) config('chatbot.brand_name');
        $supportPhone = (string) config('chatbot.support_phone');
        $supportEmail = (string) config('chatbot.support_email');
        $hours = (string) config('chatbot.hours');
        $knowledge = $this->readKnowledge();
        $payments = $this->paymentContext();
        $products = $this->productsContext();

        $userLine = $userName ? "Nombre del cliente: {$userName}." : "Cliente invitado.";
        $sessionLine = $sessionId ? "Session: {$sessionId}." : '';

        return trim(implode("\n", array_filter([
            "Eres POLL-IA, el asistente oficial de {$brand}.",
            "Responde en español, tono amable y directo.",
            "Solo responde sobre: productos, pedidos, pagos, delivery, horarios, ubicación, contacto y uso de la app/web.",
            "Si falta información, pide 1-2 datos concretos (por ejemplo código de tracking o correo).",
            "Si el usuario pide algo fuera del negocio, responde que no aplica y ofrece el contacto humano.",
            "Horario: {$hours}.",
            "Soporte: {$supportPhone} / {$supportEmail}.",
            $payments ? "Medios de pago y datos utiles:\n{$payments}" : null,
            $products ? "Productos disponibles de referencia:\n{$products}" : null,
            $userLine,
            $sessionLine,
            $knowledge ? "Base de conocimiento:\n{$knowledge}" : null,
        ])));
    }

    private function resolveProviderAndModel(): array
    {
        $provider = strtolower(trim((string) config('chatbot.provider', 'ollama')));

        if ($provider === 'openai') {
            return ['openai', (string) config('chatbot.openai.model', 'gpt-4.1-mini')];
        }

        return ['ollama', (string) config('chatbot.ollama.model', 'llama3.1:8b')];
    }

    private function readKnowledge(): ?string
    {
        $path = (string) config('chatbot.knowledge_path');
        if ($path === '' || ! is_file($path)) return null;
        $content = @file_get_contents($path);
        $content = is_string($content) ? trim($content) : '';
        return $content !== '' ? $content : null;
    }

    private function paymentContext(): ?string
    {
        $payments = (array) config('company.payments', []);
        $lines = [];

        if (($payments['yape']['enabled'] ?? false) && ! empty($payments['yape']['phone'])) {
            $lines[] = "- Yape: ".$payments['yape']['phone'];
        }
        if (($payments['plin']['enabled'] ?? false) && ! empty($payments['plin']['phone'])) {
            $lines[] = "- Plin: ".$payments['plin']['phone'];
        }
        if (($payments['mercado_pago']['enabled'] ?? false)) {
            $label = trim((string) ($payments['mercado_pago']['label'] ?? 'Mercado Pago'));
            $lines[] = "- {$label}: checkout seguro para tarjetas, cuenta Mercado Pago y Yape.";
        }
        if (($payments['cod']['enabled'] ?? false) && ! empty($payments['cod']['message'])) {
            $lines[] = '- Contraentrega: '.$payments['cod']['message'];
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
                ->orderBy('price')
                ->limit(10)
                ->get(['name', 'price', 'category', 'stock']);
        } catch (\Throwable) {
            return null;
        }

        if ($products->isEmpty()) {
            return null;
        }

        return $products->map(function (Product $product): string {
            $category = trim((string) $product->category);
            $categoryText = $category !== '' ? $category : 'general';

            return "- {$product->name} | Categoria: {$categoryText} | Precio: S/ ".number_format((float) $product->price, 2, '.', '')." | Stock: {$product->stock}";
        })->implode("\n");
    }

    private function fallback(): string
    {
        $brand = (string) config('chatbot.brand_name');
        $supportPhone = (string) config('chatbot.support_phone');
        $supportEmail = (string) config('chatbot.support_email');

        return "Ahora mismo no puedo responder con el asistente IA. Para ayudarte más rápido, escríbenos a {$supportPhone} o {$supportEmail} ({$brand}).";
    }
}
