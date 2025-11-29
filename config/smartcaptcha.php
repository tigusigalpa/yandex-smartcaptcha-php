<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OAuth Token
    |--------------------------------------------------------------------------
    |
    | Your Yandex OAuth token for API authentication.
    | Required for captcha management operations (create, update, delete, etc.)
    | The package will automatically exchange it for IAM token and refresh when needed.
    |
    */
    'oauth_token' => env('YANDEX_SMARTCAPTCHA_OAUTH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Server Secret Key
    |--------------------------------------------------------------------------
    |
    | Your SmartCaptcha server secret key for token validation.
    | This is different from IAM token and is used to validate user tokens.
    |
    */
    'secret_key' => env('YANDEX_SMARTCAPTCHA_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Client Key
    |--------------------------------------------------------------------------
    |
    | Your SmartCaptcha client key for frontend widget.
    |
    */
    'client_key' => env('YANDEX_SMARTCAPTCHA_CLIENT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Folder ID
    |--------------------------------------------------------------------------
    |
    | Your Yandex Cloud folder ID where captchas are stored.
    |
    */
    'folder_id' => env('YANDEX_SMARTCAPTCHA_FOLDER_ID'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable logging for SmartCaptcha operations.
    |
    */
    'logging' => [
        'enabled' => env('YANDEX_SMARTCAPTCHA_LOGGING', false),
        'channel' => env('YANDEX_SMARTCAPTCHA_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    |
    | Custom options for Guzzle HTTP client.
    |
    */
    'http' => [
        'timeout' => env('YANDEX_SMARTCAPTCHA_TIMEOUT', 30),
    ],
];
