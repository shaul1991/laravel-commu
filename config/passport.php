<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard Passport will use when
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Token Lifetimes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that access tokens and
    | refresh tokens should be valid for. Access tokens are short-lived
    | for security, while refresh tokens allow users to obtain new
    | access tokens without re-authenticating.
    |
    */

    'tokens_expire_in' => env('PASSPORT_TOKEN_EXPIRE_MINUTES', 15),

    'refresh_tokens_expire_in' => env('PASSPORT_REFRESH_TOKEN_EXPIRE_MINUTES', 10080), // 7 days

    'personal_access_tokens_expire_in' => env('PASSPORT_PERSONAL_TOKEN_EXPIRE_MINUTES', 10080), // 7 days

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Passport uses encryption keys while generating secure access tokens for
    | your application. By default, the keys are stored as local files but
    | can be set via environment variables when that is more convenient.
    |
    */

    'private_key' => env('PASSPORT_PRIVATE_KEY'),

    'public_key' => env('PASSPORT_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Passport Database Connection
    |--------------------------------------------------------------------------
    |
    | By default, Passport's models will utilize your application's default
    | database connection. If you wish to use a different connection you
    | may specify the configured name of the database connection here.
    |
    */

    'connection' => env('PASSPORT_CONNECTION'),

];
