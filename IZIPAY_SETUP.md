# Integracion Izipay

El proyecto usa Izipay como pasarela unica. Yape y Plin se atienden dentro del checkout seguro de Izipay, no como pagos manuales con voucher.

## Variables de entorno

Agrega estas variables en `.env` local y en Coolify/produccion:

```env
IZIPAY_ENABLED=true
IZIPAY_MODE=test
IZIPAY_API_BASE_URL=https://api.micuentaweb.pe/api-payment/V4
IZIPAY_IPN_URL=https://pollos.saborcentral.com/pagos/izipay/ipn
IZIPAY_SHOP_ID=
IZIPAY_REST_API_KEY=
IZIPAY_PUBLIC_KEY=
IZIPAY_HMAC_KEY=
IZIPAY_TIMEOUT=15
IZIPAY_JS_URL=https://static.micuentaweb.pe/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js
IZIPAY_CSS_URL=https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.css
```

Usa tus claves REST solo en `IZIPAY_REST_API_KEY`. Usa tu clave cliente JavaScript en `IZIPAY_PUBLIC_KEY`.

## URLs que debes registrar en Izipay

- URL de notificacion/IPN recomendada para Back Office: `https://tu-dominio.com/pagos/izipay/ipn`
- Alias temporal compatible: `https://tu-dominio.com/izipay-ipn`
- Alias temporal compatible: `https://tu-dominio.com/izipay-ipn.php`
- Retorno exitoso: `https://tu-dominio.com/mis-pedidos`

Para tu dominio actual, configura:

```text
https://pollos.saborcentral.com/pagos/izipay/ipn
```

Si prefieres usar una ruta Laravel sin extension, tambien queda disponible:

```text
https://pollos.saborcentral.com/izipay-ipn
```

El endpoint responde texto plano `OK` con `HTTP 200` a `GET`, `HEAD` y `POST` vacio. Una notificacion no vacia solo modifica el pedido despues de validar su HMAC y sus datos comerciales.

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
php artisan config:show services
php artisan route:list | grep izipay
```

Comprueba desde fuera de Coolify:

```sh
curl -i https://pollos.saborcentral.com/pagos/izipay/ipn
curl -I https://pollos.saborcentral.com/pagos/izipay/ipn
curl -i -X POST https://pollos.saborcentral.com/pagos/izipay/ipn
```

Los tres comandos deben llegar directamente al mismo contenedor, sin puerto `7000` en la URL, sin redireccion a login, HTTP, `www` u otro dominio, y devolver texto plano `OK`. Verifica tambien el certificado HTTPS, el proxy de dominio de Coolify y que Cloudflare, firewall o WAF permitan POST.

NGINX Unit escucha internamente en `7000`; su `fallback` envia las rutas que no son archivos a `public/index.php`. No debe publicarse un archivo PHP estatico ni existir una regla especial para `.php`. Puedes revisar la configuracion efectiva sin revelar claves con `php artisan izipay:diagnose` en entorno local.
