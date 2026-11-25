<?php

/*
| Pengaturan CORS (Cross-Origin Resource Sharing).
| Frontend Vue berjalan di alamat berbeda (mis. http://localhost:5173),
| sehingga API harus mengizinkan alamat tersebut.
| Isi CORS_ALLOWED_ORIGINS di .env, pisahkan dengan koma.
*/

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '*'))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
