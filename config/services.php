<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // 'google' => [
    //     'client_id' => env('GOOGLE_CLIENT_ID'),
    //     'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    //     'redirect' => env('GOOGLE_REDIRECT_URI'),
    // ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://gdv.devop360.com/rental-system/google/callback'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'twilio' => [
        'sid'     => env('TWILIO_SID', 'TWILIO_SID_REMOVED'),
        'token'   => env('TWILIO_TOKEN', 'TWILIO_TOKEN_REMOVED'),
        'from'    => env('TWILIO_FROM', '+19802917404'),
        'enabled' => env('TWILIO_ENABLED', true),
    ],


    'apple' => [
        'team_id' => env('APPLE_TEAM_ID', 'G2Y86RN2UA'),
        'client_id' => env('APPLE_CLIENT_ID', 'com.gdv.gameDayValet'),
        'key_id' => env('APPLE_KEY_ID', 'PJ6NRA2SUB'),
        'private_key_path' => env('APPLE_PRIVATE_KEY_PATH', storage_path('apple/AuthKey_PJ6NRA2SUB.p8')),
        'redirect' => env('APPLE_REDIRECT_URI', 'https://gdv.devop360.com/rental-system/apple/callback'),
    ],


];
