# Integracion Izipay

El proyecto usa Izipay como pasarela unica. Yape y Plin se atienden dentro del checkout seguro de Izipay, no como pagos manuales con voucher.

## Variables de entorno

Agrega estas variables en `.env` local y en Coolify/produccion:

```env
IZIPAY_ENABLED=true
IZIPAY_MODE=production
IZIPAY_API_BASE_URL=https://api.micuentaweb.pe/api-payment/V4
IZIPAY_IPN_URL=https://tu-dominio.com/izipay-ipn.php
IZIPAY_SHOP_ID=
IZIPAY_REST_API_KEY=
IZIPAY_PUBLIC_KEY=
IZIPAY_HMAC_KEY=
IZIPAY_JS_URL=https://static.micuentaweb.pe/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js
IZIPAY_CSS_URL=https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.css
```

Usa tus claves REST solo en `IZIPAY_REST_API_KEY`. Usa tu clave cliente JavaScript en `IZIPAY_PUBLIC_KEY`.

## URLs que debes registrar en Izipay

- URL de notificacion/IPN recomendada para Back Office: `https://tu-dominio.com/pagos/izipay/ipn`
- URL alternativa compatible: `https://tu-dominio.com/izipay-ipn`
- URL API interna compatible: `https://tu-dominio.com/api/v1/payments/izipay/webhook`
- URL solo para validar Back Office si la regla se pone roja: `https://tu-dominio.com/izipay-validate.php`
- Retorno exitoso: `https://tu-dominio.com/mis-pedidos`

Para tu dominio actual, configura:

```text
https://pollos.saborcentral.com/izipay-ipn.php
```

Si prefieres usar una ruta Laravel sin extension, tambien queda disponible:

```text
https://pollos.saborcentral.com/izipay-ipn
```

El endpoint responde `HTTP 200` a `GET`, `HEAD`, `POST` vacio y `POST` de validacion sin `orderId`. Cuando Izipay envie una notificacion real con `orderId`, Laravel valida la firma si configuraste `IZIPAY_HMAC_KEY` y actualiza el pedido.

Si el Back Office sigue rechazando la regla aunque `/izipay-ipn.php` responda 200, registra temporalmente:

```text
https://pollos.saborcentral.com/izipay-validate.php
```

Esa URL solo confirma localizacion del servidor. Para pagos reales, deja `IZIPAY_IPN_URL=https://pollos.saborcentral.com/izipay-ipn.php`, porque esa es la URL que Laravel envia a Izipay en cada `CreatePayment`.

## Flujo implementado

1. Web o app movil crean el pedido con `payment_method=izipay`.
2. Laravel llama a Izipay REST y obtiene `formToken`.
3. Laravel devuelve `payment_url`.
4. Web redirige a `payment_url`; Flutter abre esa URL en navegador externo.
5. Izipay firma la respuesta con HMAC; Laravel compara comercio, referencia, monto, moneda y transaccion antes de marcar el pedido como `verified`, `pending` o `rejected`.
6. La tabla `payment_transactions` registra cada intento e impide reutilizar `transaction_uuid`, haciendo idempotentes las notificaciones repetidas.

Ejecuta `php artisan migrate` despues de desplegar esta version. `IZIPAY_HMAC_KEY` es obligatorio: sin ella ningun callback puede confirmar un pago.

Despues de cambiar variables en produccion, ejecuta:

```sh
php artisan optimize:clear
php artisan optimize
```
