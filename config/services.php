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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'barcode_printer' => [
        'name' => env('BARCODE_PRINTER_NAME', 'Gainscha GS-2406T PLUS'),
        'share' => env('BARCODE_PRINTER_SHARE', '\\\\localhost\\GS2406T'),
        'fallback_share' => env('BARCODE_PRINTER_FALLBACK_SHARE', '\\\\127.0.0.1\\GS2406T'),
    ],

    'scba' => [
        'member_list_url' => env('SCBA_MEMBER_LIST_URL', 'https://api.scba.org.bd/api/esl/memberlist'),
        'verify_ssl' => env('SCBA_SSL_VERIFY', true),
        'ssl_cipher_list' => env('SCBA_SSL_CIPHER_LIST', 'DEFAULT@SECLEVEL=1'),
    ],

];
