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
        'from_address' => env('RESEND_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
        'from_name' => env('RESEND_FROM_NAME', env('MAIL_FROM_NAME')),
        'timeout' => env('RESEND_TIMEOUT', 15),
    ],

    'google_auth' => [
        'client_ids' => array_values(array_filter(array_map(
            static fn (?string $value): string => trim((string) $value),
            explode(',', (string) env(
                'GOOGLE_AUTH_CLIENT_IDS',
                '979612097533-vmule768o9q9gfe18trr2ha7kkif8r7h.apps.googleusercontent.com,979612097533-h80d392m1b9789dkd22fldogjqsaitja.apps.googleusercontent.com'
            ))
        ))),
        'web_client_id' => env(
            'GOOGLE_AUTH_WEB_CLIENT_ID',
            '979612097533-vmule768o9q9gfe18trr2ha7kkif8r7h.apps.googleusercontent.com'
        ),
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

    'mercadopago' => [
        'enabled' => (bool) env('MERCADOPAGO_ENABLED', false),
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    ],

    'apisperu_fact' => [
        'base_url' => env('APISPERU_FACT_BASE_URL', 'https://facturacion.apisperu.com/api/v1'),
        'company_token' => env('APISPERU_FACT_COMPANY_TOKEN'),
        'username' => env('APISPERU_FACT_USERNAME'),
        'password' => env('APISPERU_FACT_PASSWORD'),
        'timeout' => env('APISPERU_FACT_TIMEOUT', 30),
    ],

];
