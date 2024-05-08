<?php

return [
    'cache_key' => 'laravel_ab_user',
    'request_param'=> env('LARAVEL_AB_REQUEST_PARAM', 'abid'),
    'allow_param'=> env('LARAVEL_AB_ALLOW_PARAM', false),
    'api_key' => env('LARAVEL_AB_API_KEY', ''),
    'report_url' => env('LARAVEL_AB_REPORT_URL', '/ab/report'),
    'report_username'=> env('LARAVEL_AB_REPORT_USERNAME', null),
    'report_password'=> env('LARAVEL_AB_REPORT_PASSWORD', null),
];
