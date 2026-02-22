<?php

return [
    'enabled' => env('AUDIT_ENABLED', true),

    'channel' => env('AUDIT_CHANNEL', 'audit'),

    'log_http_requests' => env('AUDIT_LOG_HTTP_REQUESTS', true),

    'log_model_events' => env('AUDIT_LOG_MODEL_EVENTS', true),

    'skip_methods' => [
        'OPTIONS',
    ],

    'skip_paths' => [
        '_debugbar/*',
        'horizon/*',
        'telescope/*',
        'livewire/livewire.js',
        'livewire/livewire.js.map',
    ],

    'sensitive_keys' => [
        'password',
        'password_confirmation',
        'token',
        'auth_token',
        'remember_token',
        'secret',
        'authorization',
        'api_key',
        'pix_key',
        'merchant_key',
        'pagbank_token',
        'cielo_merchant_key',
    ],

    'max_value_length' => 2000,
    'max_array_items' => 80,
];

