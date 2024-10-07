<?php

return [
    'aws' => [
        'endpoint' => env('AWS_MEDIACONVERT_ENDPOINT'),
        'role' => env('AWS_MEDIACONVERT_ROLE'),
        'region' => env('AWS_MEDIACONVERT_REGION'),
        'key' => env('AWS_MEDIACONVERT_ACCESS_KEY_ID'),
        'secret' => env('AWS_MEDIACONVERT_SECRET_ACCESS_KEY'),
        'speke' => env('SPEKE_SERVER_URL'),
    ],
    'widevine' => [
        'id' => env('WIDEVINE_ID'),
    ],
    'playready' => [
        'id' => env('PLAYREADY_ID'),
    ],
    'fairplay' => [
        'id' => env('FAIRPLAY_ID'),
    ],
];
