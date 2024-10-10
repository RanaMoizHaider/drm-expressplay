<?php

return [
    'aws' => [
        'endpoint' => env('AWS_MEDIACONVERT_ENDPOINT'),
        'role' => env('AWS_MEDIACONVERT_ROLE'),
        'region' => env('AWS_MEDIACONVERT_REGION'),
        'key' => env('AWS_MEDIACONVERT_ACCESS_KEY_ID'),
        'secret' => env('AWS_MEDIACONVERT_SECRET_ACCESS_KEY'),
        'speke' => env('SPEKE_SERVER_URL'),
        'queue' => env('AWS_MEDIACONVERT_QUEUE'),
    ],
    'expressplay' => [
        'api_key' => env('EXPRESSPLAY_API_KEY'),
        'kek' => env('EXPRESSPLAY_KEK'),
        'kid' => env('EXPRESSPLAY_KID'),
        'content_key' => env('EXPRESSPLAY_K'),
        'kekid' => env('EXPRESSPLAY_KEKID'),
        'ek' => env('EXPRESSPLAY_EK'),
    ],
    'resource_id' => env('RESOURCE_ID'),
    'widevine' => [
        'id' => env('WIDEVINE_ID'),
    ],
    'marlin' => [
        'id' => env('MARLIN_ID'),
    ],
    'playready' => [
        'id' => env('PLAYREADY_ID'),
    ],
    'fairplay' => [
        'id' => env('FAIRPLAY_ID'),
    ],
];
