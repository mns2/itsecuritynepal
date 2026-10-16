<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    // IT Security Nepal
    // Google reviews (Places API New). The Place ID is set in Admin → Site settings → Google reviews.
    'google' => [
        'places_key' => env('GOOGLE_PLACES_API_KEY'),
    ],

    'enquiries' => [
        'notify' => env('ENQUIRY_NOTIFY_EMAIL'),
    ],

    // Email campaigns: messages sent per minute by `newsletter:send` (keep under your host's hourly limit / 60).
    'newsletter' => [
        'per_minute' => (int) env('NEWSLETTER_PER_MINUTE', 30),
    ],

    'analytics' => [
        'ga4' => env('GA4_MEASUREMENT_ID'),
        'gtm' => env('GTM_CONTAINER_ID'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
