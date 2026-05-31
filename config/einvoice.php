<?php

return [
    'provider' => env('EINVOICE_PROVIDER', 'apisperu'),
    'environment' => env('EINVOICE_ENVIRONMENT', 'beta'),
    'currency' => env('EINVOICE_CURRENCY', 'PEN'),
    'boleta_series' => env('EINVOICE_BOLETA_SERIES', 'B001'),
    'factura_series' => env('EINVOICE_FACTURA_SERIES', 'F001'),
    'company' => [
        'ruc' => env('COMPANY_RUC', ''),
        'razon_social' => env('COMPANY_LEGAL_NAME', ''),
        'nombre_comercial' => env('COMPANY_BRAND_NAME', ''),
        'email' => env('COMPANY_SUPPORT_EMAIL', ''),
        'telephone' => env('COMPANY_SUPPORT_PHONE', ''),
        'address' => [
            'direccion' => env('COMPANY_FISCAL_ADDRESS', ''),
            'departamento' => env('COMPANY_FISCAL_DEPARTMENT', ''),
            'provincia' => env('COMPANY_FISCAL_PROVINCE', ''),
            'distrito' => env('COMPANY_FISCAL_DISTRICT', ''),
            'ubigueo' => env('COMPANY_FISCAL_UBIGEO', ''),
            'codigo_pais' => env('COMPANY_FISCAL_COUNTRY_CODE', 'PE'),
            'cod_local' => env('COMPANY_FISCAL_LOCAL_CODE', '0000'),
        ],
    ],
];
