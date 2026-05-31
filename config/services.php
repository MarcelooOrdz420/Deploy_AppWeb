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

    'reniec' => [
        'lookup_url' => env('RENIEC_LOOKUP_URL'),
        'token' => env('RENIEC_API_TOKEN'),
        'auth_mode' => env('RENIEC_AUTH_MODE', 'query'),
        'token_query_param' => env('RENIEC_TOKEN_QUERY_PARAM', 'token'),
        'timeout' => env('RENIEC_TIMEOUT', 15),
    ],

    'sunat' => [
        'lookup_url' => env('SUNAT_LOOKUP_URL'),
        'token' => env('SUNAT_API_TOKEN'),
        'auth_mode' => env('SUNAT_AUTH_MODE', 'query'),
        'token_query_param' => env('SUNAT_TOKEN_QUERY_PARAM', 'token'),
        'token_url' => env('SUNAT_TOKEN_URL'),
        'grant_type' => env('SUNAT_GRANT_TYPE', 'password'),
        'client_id' => env('SUNAT_CLIENT_ID'),
        'client_secret' => env('SUNAT_CLIENT_SECRET'),
        'scope' => env('SUNAT_SCOPE'),
        'username' => env('SUNAT_USERNAME'),
        'password' => env('SUNAT_PASSWORD'),
        'timeout' => env('SUNAT_TIMEOUT', 15),
    ],

    'apisperu_dniruc' => [
        'base_url' => env('APISPERU_DNIRUC_BASE_URL', 'https://dniruc.apisperu.com/api/v1'),
        'token' => env('APISPERU_DNIRUC_TOKEN'),
        'auth_mode' => env('APISPERU_DNIRUC_AUTH_MODE', 'query'),
        'token_query_param' => env('APISPERU_DNIRUC_TOKEN_QUERY_PARAM', 'token'),
        'timeout' => env('APISPERU_DNIRUC_TIMEOUT', 15),
    ],

    'culqi' => [
        'enabled' => (bool) env('CULQI_ENABLED', false),
        'reference_only' => (bool) env('CULQI_REFERENCE_ONLY', true),
        'mode' => env('CULQI_MODE', 'test'),
        'public_key' => env('CULQI_PUBLIC_KEY'),
        'secret_key' => env('CULQI_SECRET_KEY'),
        'rsa_id' => env('CULQI_RSA_ID'),
        'rsa_public_key' => env('CULQI_RSA_PUBLIC_KEY'),
    ],

    'apisperu_fact' => [
        'base_url' => env('APISPERU_FACT_BASE_URL', 'https://facturacion.apisperu.com/api/v1'),
        'company_token' => env('APISPERU_FACT_COMPANY_TOKEN'),
        'username' => env('APISPERU_FACT_USERNAME'),
        'password' => env('APISPERU_FACT_PASSWORD'),
        'timeout' => env('APISPERU_FACT_TIMEOUT', 30),
    ],

];
