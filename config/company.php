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
        'izipay' => [
            'label' => env('COMPANY_IZIPAY_LABEL', 'Pago seguro con Izipay'),
            'message' => env('COMPANY_IZIPAY_MESSAGE', 'Paga con tarjeta desde el checkout seguro de Izipay.'),
            'enabled' => (bool) env('IZIPAY_ENABLED', true),
        ],
    ],
];
