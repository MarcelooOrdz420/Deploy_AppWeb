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
}
