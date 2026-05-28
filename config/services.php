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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    /*
     * Brevo (ex-Sendinblue) — transactional mail provider hosted in Paris (FR).
     * Free tier 300/day covers Phase 1 contact-form volume. Symfony transport
     * registered in AppServiceProvider via `Mail::extend('brevo', …)`.
     * Endpoint reference: https://api.brevo.com
     */
    'brevo' => [
        'key' => env('BREVO_API_KEY'),
        'endpoint' => env('BREVO_ENDPOINT', 'https://api.brevo.com'),
    ],

];
