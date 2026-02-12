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

    'pesapal' => [
        'base_url' => env('PESAPAL_BASE_URL', 'https://pay.pesapal.com/v3'),
        'consumer_key' => env('PESAPAL_CONSUMER_KEY'),
        'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
        'callback_url' => env('PESAPAL_CALLBACK_URL', env('APP_URL').'/payments/pesapal/callback'),
        'ipn_url' => env('PESAPAL_IPN_URL', env('APP_URL').'/payments/pesapal/ipn'),
        'ipn_id' => env('PESAPAL_IPN_ID'),
        'currency' => env('PESAPAL_CURRENCY', 'UGX'),
        'country_code' => env('PESAPAL_COUNTRY_CODE', 'UG'),
    ],

];
