# Nubefact y JWT en Postman

## Variables de entorno Laravel

Para emitir comprobantes con Nubefact configura:

```env
EINVOICE_PROVIDER=nubefact
EINVOICE_BOLETA_SERIES=B001
EINVOICE_FACTURA_SERIES=F001
EINVOICE_AUTO_SEND=false

NUBEFACT_ROUTE=https://api.nubefact.com/api/v1/TU-RUTA
NUBEFACT_TOKEN=TU_TOKEN_NUBEFACT
NUBEFACT_SEND_TO_SUNAT=true
NUBEFACT_SEND_TO_CUSTOMER=false
NUBEFACT_PDF_FORMAT=TICKET
```

Nubefact no entrega tokens JWT. Su autenticacion usa el token de Nubefact en el header `Authorization`.

## Probar JWT de tu API en Postman

1. Inicia sesion:

```http
POST {{base_url}}/api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@eldorado.pe",
  "password": "admin12345"
}
```

La respuesta debe incluir:

```json
{
  "token": "eyJ...",
  "user": {}
}
```

2. Guarda el token en Postman y usalo en requests protegidos:

```http
Authorization: Bearer {{jwt_token}}
Accept: application/json
```

3. Verifica el token:

```http
GET {{base_url}}/api/v1/auth/me
Authorization: Bearer {{jwt_token}}
Accept: application/json
```

## Probar preview de Nubefact

Este endpoint no emite en Nubefact; solo muestra el JSON que se enviaria:

```http
GET {{base_url}}/api/v1/admin/orders/{{order_id}}/einvoice/preview
Authorization: Bearer {{jwt_token}}
Accept: application/json
```

## Emitir comprobante en Nubefact

Este endpoint envia el comprobante real a Nubefact:

```http
POST {{base_url}}/api/v1/admin/orders/{{order_id}}/einvoice/send
Authorization: Bearer {{jwt_token}}
Accept: application/json
```

El pedido debe tener `billing_receipt_type` como `boleta` o `factura`, documento del cliente, nombre fiscal y estado de pago verificado.
