<?php

return [
    'provider' => env('AI_PROVIDER', 'mock'),
    'max_retries' => (int) env('AI_MAX_RETRIES', 3),
    'retry_delay_seconds' => (int) env('AI_RETRY_DELAY_SECONDS', 60),

    'qwen' => [
        'base_url' => env('QWEN_API_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
        'api_key' => env('QWEN_API_KEY'),
        'model' => env('QWEN_MODEL', 'qwen/qwen3-coder-480b-a35b-instruct'),
        'temperature' => (float) env('QWEN_TEMPERATURE', 0.2),
        'timeout_seconds' => (int) env('QWEN_TIMEOUT_SECONDS', 30),
    ],

    'mock' => [
        'model' => env('AI_MOCK_MODEL', 'mock-v1'),
    ],
];
