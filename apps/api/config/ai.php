<?php

return [
    'provider' => env('AI_PROVIDER', 'mock'),
    'max_retries' => (int) env('AI_MAX_RETRIES', 3),
    'retry_delay_seconds' => (int) env('AI_RETRY_DELAY_SECONDS', 60),

    'mock' => [
        'model' => env('AI_MOCK_MODEL', 'mock-v1'),
    ],
];
