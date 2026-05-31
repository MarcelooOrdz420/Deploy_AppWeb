<?php

return [
    'brand_name' => env('COMPANY_BRAND_NAME', 'Pollos y Parrillas El Dorado'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Pollos y Parrillas El Dorado S.A.C.'),
    'ruc' => env('COMPANY_RUC', ''),
    'support_phone' => env('COMPANY_SUPPORT_PHONE', ''),
    'support_email' => env('COMPANY_SUPPORT_EMAIL', ''),
    'currency' => env('COMPANY_CURRENCY', 'PEN'),
    'payments' => [
        'yape' => [
            'label' => env('COMPANY_YAPE_LABEL', 'Yape Empresa'),
            'phone' => env('COMPANY_YAPE_PHONE', ''),
            'qr_path' => env('COMPANY_YAPE_QR_PATH', '/images/yape-qr.png'),
            'enabled' => (bool) env('COMPANY_YAPE_ENABLED', true),
        ],
        'plin' => [
            'label' => env('COMPANY_PLIN_LABEL', 'Plin Empresa'),
            'phone' => env('COMPANY_PLIN_PHONE', ''),
            'qr_path' => env('COMPANY_PLIN_QR_PATH', '/images/plin-qr.png'),
            'enabled' => (bool) env('COMPANY_PLIN_ENABLED', true),
        ],
        'transfer' => [
            'label' => env('COMPANY_TRANSFER_LABEL', 'Transferencia bancaria'),
            'bank_name' => env('COMPANY_BANK_NAME', 'BCP'),
            'account_number' => env('COMPANY_BANK_ACCOUNT', ''),
            'cci' => env('COMPANY_BANK_CCI', ''),
            'account_holder' => env('COMPANY_BANK_ACCOUNT_HOLDER', ''),
            'enabled' => (bool) env('COMPANY_TRANSFER_ENABLED', true),
        ],
        'cod' => [
            'label' => env('COMPANY_COD_LABEL', 'Pago contraentrega'),
            'message' => env('COMPANY_COD_MESSAGE', 'Pagas cuando recibes tu pedido.'),
            'enabled' => (bool) env('COMPANY_COD_ENABLED', true),
        ],
        'culqi' => [
            'label' => env('COMPANY_CULQI_LABEL', 'Culqi'),
            'enabled' => (bool) env('COMPANY_CULQI_ENABLED', false),
        ],
    ],
];
