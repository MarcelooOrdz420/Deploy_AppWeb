<?php

return [
    'provider' => env('CHATBOT_PROVIDER', 'ollama'),
    'brand_name' => env('COMPANY_BRAND_NAME', env('APP_NAME', 'El Dorado')),
    'support_phone' => env('COMPANY_SUPPORT_PHONE', ''),
    'support_email' => env('COMPANY_SUPPORT_EMAIL', ''),
    'hours' => env('COMPANY_HOURS', '11:00 a. m. a 10:00 p. m.'),
    'knowledge_path' => base_path('resources/chatbot/knowledge.md'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
        'timeout' => env('OPENAI_TIMEOUT', 25),
    ],

    'ollama' => [
        'base_url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.1:8b'),
        'timeout' => env('OLLAMA_TIMEOUT', 60),
        'temperature' => env('OLLAMA_TEMPERATURE', 0.4),
        'num_predict' => env('OLLAMA_NUM_PREDICT', 350),
    ],
];
