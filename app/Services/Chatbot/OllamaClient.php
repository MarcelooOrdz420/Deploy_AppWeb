<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;

class OllamaClient
{
    public function respond(string $model, string $system, string $user): string
    {
        $baseUrl = rtrim((string) config('chatbot.ollama.base_url', 'http://127.0.0.1:11434'), '/');
        $timeout = (int) config('chatbot.ollama.timeout', 60);

        $res = Http::acceptJson()
            ->asJson()
            ->connectTimeout(8)
            ->timeout($timeout)
            ->post($baseUrl.'/api/chat', [
                'model' => $model,
                'stream' => false,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $system,
                    ],
                    [
                        'role' => 'user',
                        'content' => $user,
                    ],
                ],
                'options' => array_filter([
                    'temperature' => config('chatbot.ollama.temperature'),
                    'num_predict' => config('chatbot.ollama.num_predict'),
                ], static fn ($value) => $value !== null && $value !== ''),
            ]);

        if (! $res->ok()) {
            $status = $res->status();
            $body = substr((string) $res->body(), 0, 3000);
            throw new \RuntimeException("Ollama error {$status}: {$body}");
        }

        $json = $res->json();
        $text = trim((string) data_get($json, 'message.content', ''));

        if ($text !== '') {
            return $text;
        }

        throw new \RuntimeException('Ollama devolvio una respuesta vacia');
    }

    public function status(string $model): array
    {
        $baseUrl = rtrim((string) config('chatbot.ollama.base_url', 'http://127.0.0.1:11434'), '/');

        try {
            $res = Http::acceptJson()
                ->connectTimeout(4)
                ->timeout(8)
                ->get($baseUrl.'/api/tags');

            if (! $res->ok()) {
                return [
                    'ok' => false,
                    'base_url' => $baseUrl,
                    'model' => $model,
                    'message' => 'Ollama respondio con HTTP '.$res->status().'.',
                ];
            }

            $models = collect((array) $res->json('models', []))
                ->map(fn ($item): string => (string) data_get($item, 'name', ''))
                ->filter()
                ->values();

            return [
                'ok' => true,
                'base_url' => $baseUrl,
                'model' => $model,
                'model_installed' => $models->contains($model),
                'installed_models' => $models->all(),
                'message' => $models->contains($model)
                    ? 'Ollama esta disponible y el modelo configurado esta instalado.'
                    : 'Ollama esta disponible, pero el modelo configurado no aparece instalado.',
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'base_url' => $baseUrl,
                'model' => $model,
                'message' => 'No se pudo conectar con Ollama: '.$e->getMessage(),
            ];
        }
    }
}
