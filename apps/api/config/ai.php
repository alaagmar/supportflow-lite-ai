<?php

return [
    'provider' => env('AI_PROVIDER', 'mock'),
    'max_retries' => (int) env('AI_MAX_RETRIES', 3),
    'retry_delay_seconds' => (int) env('AI_RETRY_DELAY_SECONDS', 60),

    'mistral' => [
        'base_url' => env('MISTRAL_API_BASE_URL', 'https://api.mistral.ai/v1'),
        'api_key' => env('MISTRAL_API_KEY'),
        'model' => env('MISTRAL_MODEL', 'ministral-3b-2512'),
        'temperature' => (float) env('MISTRAL_TEMPERATURE', 0.2),
        'timeout_seconds' => (int) env('MISTRAL_TIMEOUT_SECONDS', 30),
    ],

    'mock' => [
        'model' => env('AI_MOCK_MODEL', 'mock-v1'),
    ],
];
