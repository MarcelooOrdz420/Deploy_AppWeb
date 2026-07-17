# Integracion Izipay

El proyecto usa Izipay como pasarela unica. Yape y Plin se atienden dentro del checkout seguro de Izipay, no como pagos manuales con voucher.

## Variables de entorno

Agrega estas variables en `.env` local y en Coolify/produccion:

```env
IZIPAY_ENABLED=true
IZIPAY_MODE=test
IZIPAY_API_BASE_URL=https://api.micuentaweb.pe/api-payment/V4
IZIPAY_IPN_URL=https://NOMBRE-DEL-WORKER.workers.dev
IZIPAY_REQUIRE_RELAY=true
IZIPAY_RELAY_SECRET=
IZIPAY_SHOP_ID=
IZIPAY_REST_API_KEY=
IZIPAY_PUBLIC_KEY=
IZIPAY_HMAC_KEY=
IZIPAY_TIMEOUT=15
IZIPAY_JS_URL=https://static.micuentaweb.pe/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js
IZIPAY_CSS_URL=https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.css
```

Usa tus claves REST solo en `IZIPAY_REST_API_KEY`. Usa tu clave cliente JavaScript en `IZIPAY_PUBLIC_KEY`.

## Relay de Cloudflare Worker

El Worker completo está en `cloudflare-worker/izipay-relay-worker.mjs`. Para desplegarlo desde el panel:

1. En Cloudflare abre **Workers & Pages**, pulsa **Create**, luego **Create Worker**.
2. Abre **Edit code**, reemplaza todo por el contenido del archivo del repositorio y pulsa **Deploy**.
3. En **Settings > Variables and Secrets**, agrega `RELAY_SECRET` como **Secret**. Usa un valor aleatorio largo, por ejemplo el resultado local de `openssl rand -hex 32`.
4. Coloca exactamente el mismo valor en `IZIPAY_RELAY_SECRET` del entorno Laravel y ejecuta `php artisan optimize:clear`.
5. Define `IZIPAY_REQUIRE_RELAY=true` y `IZIPAY_IPN_URL=https://NOMBRE-DEL-WORKER.workers.dev`.
6. Registra esa URL `workers.dev` como URL de notificación en Izipay. No registres como IPN la URL canónica de Laravel.

El secreto del relay autentica al Worker ante Laravel, pero no sustituye `IZIPAY_HMAC_KEY`: Laravel valida ambas capas. El Worker no registra ni transforma `kr-answer`.

## URLs que debes registrar en Izipay

- URL de notificacion/IPN: `https://NOMBRE-DEL-WORKER.workers.dev`
- Alias temporal compatible: `https://tu-dominio.com/izipay-ipn`
- Alias temporal compatible: `https://tu-dominio.com/izipay-ipn.php`
- Retorno exitoso: `https://tu-dominio.com/mis-pedidos`

El Worker reenvía internamente hacia:

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

Comprueba el Worker y la protección del origen:

```sh
curl -i https://NOMBRE-DEL-WORKER.workers.dev
curl -I https://NOMBRE-DEL-WORKER.workers.dev
curl -i -X POST https://NOMBRE-DEL-WORKER.workers.dev
curl -i -X POST https://pollos.saborcentral.com/pagos/izipay/ipn -H "X-Relay-Secret: incorrecto"
curl -i -X POST https://pollos.saborcentral.com/pagos/izipay/ipn -H "X-Relay-Secret: TU_SECRETO"
```

GET y HEAD del Worker deben devolver 200. Un POST vacío reenviado con el secreto correcto debe devolver 200; el secreto incorrecto en el origen debe devolver 401. Una notificación real solo confirma el pago si además contiene el HMAC y los datos comerciales válidos.

Ejecuta las pruebas unitarias del Worker con `npm run test:izipay-worker` y las de Laravel con `php artisan test --filter=IzipayWebhookTest`.

Estas comprobaciones locales no demuestran el flujo productivo. La confirmación final requiere ejecutar un pago real de prueba desde Izipay y revisar los estados HTTP del Worker y Laravel sin registrar el contenido sensible.

NGINX Unit escucha internamente en `7000`; su `fallback` envia las rutas que no son archivos a `public/index.php`. No debe publicarse un archivo PHP estatico ni existir una regla especial para `.php`. Puedes revisar la configuracion efectiva sin revelar claves con `php artisan izipay:diagnose` en entorno local.
