<?php

/**
 * CORS Configuration
 * ------------------
 * Menggantikan package barryvdh/laravel-cors yang sudah deprecated.
 * Laravel 9+ sudah punya CORS handling bawaan via fruitcake/php-cors.
 *
 * Edit FRONTEND_URL di .env untuk membatasi origin ke domain spesifik.
 */

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
