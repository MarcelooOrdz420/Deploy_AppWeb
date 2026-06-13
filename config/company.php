<?php

return [
    'brand_name' => env('COMPANY_BRAND_NAME', 'Pollos y Parrillas El Dorado'),
    'legal_name' => env('COMPANY_LEGAL_NAME', 'Pollos y Parrillas El Dorado S.A.C.'),
    'ruc' => env('COMPANY_RUC', ''),
    'support_phone' => env('COMPANY_SUPPORT_PHONE', ''),
    'support_email' => env('COMPANY_SUPPORT_EMAIL', ''),
    'currency' => env('COMPANY_CURRENCY', 'PEN'),
    'location' => [
        'location_name' => env('COMPANY_LOCATION_NAME', 'Local principal'),
        'address' => env('COMPANY_LOCATION_ADDRESS', 'Jr. Cuzco, Huancayo, Peru'),
        'reference' => env('COMPANY_LOCATION_REFERENCE', 'Zona comercial cercana a Rock and Pop'),
        'google_maps_url' => env('COMPANY_GOOGLE_MAPS_URL', 'https://maps.google.com/?q=Jr.%20Cuzco%20Huancayo%20Peru'),
        'google_maps_embed_url' => env('COMPANY_GOOGLE_MAPS_EMBED_URL', 'https://maps.google.com/maps?q=Jr.%20Cuzco%20Huancayo%20Peru&t=&z=16&ie=UTF8&iwloc=&output=embed'),
        'business_hours' => env('COMPANY_BUSINESS_HOURS', 'Atencion continua hasta las 11:00 PM'),
        'service_modes' => env('COMPANY_SERVICE_MODES', 'Atencion en local, recojo y delivery'),
        'delivery_notes' => env('COMPANY_DELIVERY_NOTES', 'Envia una referencia visible como color de puerta, piso o negocio cercano.'),
        'pickup_notes' => env('COMPANY_PICKUP_NOTES', 'Programa la hora si buscas evitar espera en hora pico.'),
    ],
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
        'cod' => [
            'label' => env('COMPANY_COD_LABEL', 'Pago contraentrega'),
            'message' => env('COMPANY_COD_MESSAGE', 'Pagas cuando recibes tu pedido.'),
            'enabled' => (bool) env('COMPANY_COD_ENABLED', true),
        ],
        'mercado_pago' => [
            'label' => env('COMPANY_MERCADOPAGO_LABEL', 'Mercado Pago'),
            'enabled' => (bool) env('MERCADOPAGO_ENABLED', false),
        ],
    ],
];
