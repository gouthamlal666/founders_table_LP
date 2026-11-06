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

    'founders' => [
        // Comma-separated inboxes that get an email for every new submission. Empty = no emails.
        'mail_to' => env('FOUNDERS_MAIL_TO'),
        // Folder holding the landing page's index.html. Default: the folder above backend/.
        // A relative LANDING_PATH (e.g. "resources/landing") is read from the app folder.
        'landing_path' => (function () {
            $path = env('LANDING_PATH');
            if (! $path) {
                return dirname(base_path());
            }

            return str_starts_with($path, '/') ? $path : base_path($path);
        })(),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
