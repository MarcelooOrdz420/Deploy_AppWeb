# Variables `.env` para hosting

Usa esta lista para Coolify/hosting antes de levantar produccion. Los valores de ejemplo deben cambiarse por los reales.

## Base Laravel

```env
APP_NAME="Pollos y Parrillas El Dorado"
APP_ENV=production
APP_KEY=base64:TU_APP_KEY_GENERADA
APP_DEBUG=false
APP_URL=https://tu-dominio.com
API_BASE_URL=
APP_TIMEZONE=America/Lima
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
LOG_LEVEL=warning
```

Genera `APP_KEY` una vez con:

```bash
php artisan key:generate --show
```

## Base de datos, sesiones, cache y colas

```env
DB_CONNECTION=mysql
DB_HOST=TU_HOST_DB
DB_PORT=3306
DB_DATABASE=TU_DB
DB_USERNAME=TU_USUARIO
DB_PASSWORD=TU_PASSWORD

SESSION_DRIVER=database
SESSION_DOMAIN=
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

Ejecuta en post-deploy:

```bash
php artisan migrate --force && php artisan storage:link && php artisan optimize
```

## CORS

```env
CORS_ALLOWED_ORIGINS=https://tu-dominio.com,https://www.tu-dominio.com
```

No uses `*` en produccion.

## Correo real con Resend

El proyecto ya envia promociones, OTP y recuperacion de clave usando Resend si `RESEND_API_KEY` existe.

```env
RESEND_API_KEY=re_xxxxxxxxx
RESEND_FROM_ADDRESS=ventas@tu-dominio.com
RESEND_FROM_NAME="Pollos y Parrillas El Dorado"
RESEND_TIMEOUT=15

MAIL_MAILER=log
MAIL_FROM_ADDRESS=ventas@tu-dominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

Importante: `RESEND_FROM_ADDRESS` debe pertenecer a un dominio verificado en Resend. Si no configuras Resend, debes configurar SMTP real.

## Google Login

```env
GOOGLE_AUTH_CLIENT_IDS=CLIENTE_WEB.apps.googleusercontent.com,CLIENTE_ANDROID.apps.googleusercontent.com
GOOGLE_AUTH_WEB_CLIENT_ID=CLIENTE_WEB.apps.googleusercontent.com
```

Las cuentas registradas con Google no pueden cambiar contrasena desde el flujo normal.

## Ollama / POLL-IA

Si no puedes usar VPS, Ollama ni APIs de pago, usa el modo local:

```env
CHATBOT_PROVIDER=local
```

Ese modo no usa credenciales externas. Responde con productos de la base de datos, pagos, horarios, ubicacion, delivery y el contenido de `resources/chatbot/knowledge.md`.

Para usar Ollama:

```env
CHATBOT_PROVIDER=ollama
CHATBOT_ENABLE_OLLAMA=true
OLLAMA_URL=http://host.docker.internal:11434
OLLAMA_BASE_URL=
OLLAMA_API_KEY=
OLLAMA_MODEL=llama3.1:8b
OLLAMA_TIMEOUT=90
OLLAMA_TIMEOUT_SECONDS=
OLLAMA_TEMPERATURE=0.35
OLLAMA_NUM_PREDICT=450
OLLAMA_KEEP_ALIVE=10m
```

Para Ollama Cloud en `ollama.com`, usa `OLLAMA_BASE_URL=https://ollama.com`, deja `OLLAMA_URL` vacio y configura `OLLAMA_API_KEY`.

Comprueba:

```text
GET https://tu-dominio.com/api/v1/chatbot/status
```

La web y la app movil consumen Laravel, no Ollama directo:

```text
POST https://tu-dominio.com/api/v1/chatbot/message
```

Si Laravel corre en Docker/Coolify y Ollama en el mismo servidor host, usa `OLLAMA_URL=http://host.docker.internal:11434`. Si Ollama corre como servicio en la misma red interna de Coolify, usa `OLLAMA_URL=http://ollama:11434`.

Si tu hosting no permite instalar Ollama, instala Ollama en un VPS/servidor externo y usa `OLLAMA_URL=http://IP_PRIVADA_DEL_SERVIDOR:11434`. Si lo publicas con HTTPS detras de un proxy protegido, usa `OLLAMA_URL=https://ia.tudominio.com` y rellena `OLLAMA_API_KEY`.

## Pusher realtime

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=TU_APP_ID
PUSHER_APP_KEY=TU_APP_KEY
PUSHER_APP_SECRET=TU_APP_SECRET
PUSHER_APP_CLUSTER=mt1
PUSHER_PORT=443
PUSHER_SCHEME=https
```

Tambien actualiza en la app movil `assets/runtime_config.json`:

```json
{
  "api_origin": "https://tu-dominio.com",
  "pusher": {
    "app_key": "TU_APP_KEY",
    "cluster": "mt1",
    "use_tls": true,
    "notifications_channel": "mi-canal"
  }
}
```

## Firebase Cloud Messaging

```env
FCM_PROJECT_ID=tu-project-id
FCM_SERVICE_ACCOUNT_JSON={"type":"service_account",...}
```

Puedes usar `FCM_SERVICE_ACCOUNT_PATH` si prefieres montar el JSON como archivo privado.

## Mercado Pago

```env
MERCADOPAGO_ENABLED=true
MERCADOPAGO_PUBLIC_KEY=APP_USR_xxxxx
MERCADOPAGO_ACCESS_TOKEN=APP_USR_xxxxx
COMPANY_MERCADOPAGO_LABEL="Mercado Pago"
```

Configura el webhook en Mercado Pago:

```text
https://tu-dominio.com/api/v1/payments/mercado-pago/webhook
```

## Datos de negocio y pagos manuales

```env
COMPANY_BRAND_NAME="Pollos y Parrillas El Dorado"
COMPANY_LEGAL_NAME="Pollos y Parrillas El Dorado S.A.C."
COMPANY_RUC=TU_RUC
COMPANY_SUPPORT_PHONE=999888777
COMPANY_SUPPORT_EMAIL=ventas@tu-dominio.com
COMPANY_CURRENCY=PEN

COMPANY_YAPE_LABEL="Yape Empresa"
COMPANY_YAPE_PHONE=999888777
COMPANY_YAPE_QR_PATH=/images/yape-qr.png
COMPANY_YAPE_ENABLED=true

COMPANY_PLIN_LABEL="Plin Empresa"
COMPANY_PLIN_PHONE=999888777
COMPANY_PLIN_QR_PATH=/images/plin-qr.png
COMPANY_PLIN_ENABLED=true

COMPANY_COD_LABEL="Pago contraentrega"
COMPANY_COD_MESSAGE="Pagas cuando recibes tu pedido."
COMPANY_COD_ENABLED=true
```

## DNI/RUC y facturacion electronica

```env
APISPERU_DNIRUC_BASE_URL=https://dniruc.apisperu.com/api/v1
APISPERU_DNIRUC_TOKEN=TU_TOKEN
APISPERU_DNIRUC_AUTH_MODE=query
APISPERU_DNIRUC_TOKEN_QUERY_PARAM=token

EINVOICE_PROVIDER=apisperu
EINVOICE_ENVIRONMENT=produccion
EINVOICE_CURRENCY=PEN
EINVOICE_BOLETA_SERIES=B001
EINVOICE_FACTURA_SERIES=F001
COMPANY_FISCAL_ADDRESS="Direccion fiscal"
COMPANY_FISCAL_DEPARTMENT=JUNIN
COMPANY_FISCAL_PROVINCE=HUANCAYO
COMPANY_FISCAL_DISTRICT=HUANCAYO
COMPANY_FISCAL_UBIGEO=120101
COMPANY_FISCAL_COUNTRY_CODE=PE
COMPANY_FISCAL_LOCAL_CODE=0000

APISPERU_FACT_BASE_URL=https://facturacion.apisperu.com/api/v1
APISPERU_FACT_COMPANY_TOKEN=TU_TOKEN_EMPRESA
APISPERU_FACT_USERNAME=TU_USUARIO
APISPERU_FACT_PASSWORD=TU_PASSWORD
```
