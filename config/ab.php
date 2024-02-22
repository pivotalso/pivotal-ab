<?php

return [
    'cache_key' => 'laravel_ab_user',
    'request_param'=> env('LARAVEL_AB_REQUEST_PARAM', 'abid'),
    'allow_param'=> env('LARAVEL_AB_ALLOW_PARAM', false),
    'api_key' => env('LARAVEL_AB_API_KEY', ''),
    'api_url' => env('LARAVEL_AB_API_URL', 'https://ab.yosc.xyz'),
];
