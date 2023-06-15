<?php
    return [
        'shopify_api_key' => env('SHOPIFY_API_KEY', 'e655c879cf7e3053d340ccc33d523f83'),
        'shopify_api_secret' => env('SHOPIFY_API_SECRET', 'daec5fa7a8cf7a9ba25f75e3cbf6e287'),
        'shopify_api_version' => env('SHOPIFY_API_VERSION', '2023-01'),
        'api_scopes' => env('API_SCOPES', "read_products,write_products"),
        'ngrok_url' => env('NGROK_URL', 'https://3ce1-115-79-6-153.ngrok-free.app/'),
    ];