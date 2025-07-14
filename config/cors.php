<?php

return [
    'paths' => ['api/*', 'pricing.json','sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://dev.fixmymind.org',
        'https://fixmymind.org',
        'http://localhost:3000',
        'http://localhost:8081',
        'http://localhost:8080',
        'http://localhost:19006', // Expo web development server
        'https://localhost:19006', // Expo web development server (HTTPS)
        'exp://localhost:19000', // Expo development server
        'exp://localhost:19001', // Expo development server
        'exp://localhost:19002', // Expo development server
        'exp://192.168.1.1:19000', // Expo development server (LAN)
        'exp://10.0.0.1:19000', // Expo development server (LAN)
        '*', // Allow all origins for development (remove this in production)
    ],

    'allowed_origins_patterns' => [
        '/^https?:\/\/localhost(:\d+)?$/',
        '/^https?:\/\/127\.0\.0\.1(:\d+)?$/',
        '/^exp:\/\/.*$/', // Expo development URLs
        '/^https?:\/\/.*\.ngrok\.io$/', // ngrok tunnels for testing
    ],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'Origin'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
