<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://dev.fixmymind.org'],

    // Previous specific origins (for reference):
    // 'https://dev.fixmymind.org',
    // 'https://fixmymind.org',
    // 'http://localhost:3000',
    // 'http://localhost:8081',
    // 'https://api.fixmymind.org',

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'Origin'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
