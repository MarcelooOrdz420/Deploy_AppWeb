# Integracion Izipay

El proyecto usa Izipay como pasarela unica. Yape y Plin se atienden dentro del checkout seguro de Izipay, no como pagos manuales con voucher.

## Variables de entorno

Agrega estas variables en `.env` local y en Coolify/produccion:

```env
IZIPAY_ENABLED=true
IZIPAY_MODE=production
IZIPAY_API_BASE_URL=https://api.micuentaweb.pe/api-payment/V4
IZIPAY_SHOP_ID=
IZIPAY_REST_API_KEY=
IZIPAY_PUBLIC_KEY=
IZIPAY_HMAC_KEY=
IZIPAY_JS_URL=https://static.micuentaweb.pe/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js
IZIPAY_CSS_URL=https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.css
```

Usa tus claves REST solo en `IZIPAY_REST_API_KEY`. Usa tu clave cliente JavaScript en `IZIPAY_PUBLIC_KEY`.

## URLs que debes registrar en Izipay

- IPN/Webhook: `https://tu-dominio.com/api/v1/payments/izipay/webhook`
- Retorno exitoso: `https://tu-dominio.com/mis-pedidos`

## Flujo implementado

1. Web o app movil crean el pedido con `payment_method=izipay`.
2. Laravel llama a Izipay REST y obtiene `formToken`.
3. Laravel devuelve `payment_url`.
4. Web redirige a `payment_url`; Flutter abre esa URL en navegador externo.
5. Izipay confirma el pago por webhook y Laravel marca el pedido como `verified`, `pending` o `rejected`.

Despues de cambiar variables en produccion, ejecuta:

```sh
php artisan optimize:clear
php artisan optimize
```
