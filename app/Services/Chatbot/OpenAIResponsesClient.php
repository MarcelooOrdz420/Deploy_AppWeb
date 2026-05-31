<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;

class OpenAIResponsesClient
{
    public function respond(string $model, string $system, string $user): string
    {
        $key = (string) config('chatbot.openai.api_key');
        if (trim($key) === '') {
            throw new \RuntimeException('OPENAI_API_KEY no configurado');
        }

        $res = Http::withToken($key)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('chatbot.openai.timeout', 25))
            ->post('https://api.openai.com/v1/responses', [
                'model' => $model,
                'input' => [
                    [
                        'role' => 'system',
                        'content' => [
                            ['type' => 'input_text', 'text' => $system],
                        ],
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'input_text', 'text' => $user],
                        ],
                    ],
                ],
            ]);

        if (! $res->ok()) {
            $status = $res->status();
            $body = substr((string) $res->body(), 0, 3000);
            throw new \RuntimeException("OpenAI error {$status}: {$body}");
        }

        $json = $res->json();
        $direct = trim((string) ($json['output_text'] ?? ''));
        if ($direct !== '') return $direct;

        $chunks = [];
        foreach (($json['output'] ?? []) as $output) {
            if (! is_array($output)) continue;
            foreach (($output['content'] ?? []) as $content) {
                if (! is_array($content)) continue;
                $type = (string) ($content['type'] ?? '');
                if (! in_array($type, ['output_text', 'text'], true)) continue;
                $text = trim((string) ($content['text'] ?? ''));
                if ($text !== '') $chunks[] = $text;
            }
        }

        return trim(implode("\n\n", $chunks));
    }
}

