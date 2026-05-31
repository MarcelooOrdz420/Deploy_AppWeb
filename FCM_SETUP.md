# FCM (Firebase Cloud Messaging) - Configuración en Hosting

El backend ya soporta envío de notificaciones por FCM vía `App\\Services\\Fcm\\FcmClient`.

## Qué envía hoy

- Promociones: Admin -> Promociones (`/api/v1/admin/notifications/offers`) envía a topics `promo_all` / `promo_mobile`.
- Pedidos: cuando el admin cambia el **estado** o **estado de pago**, se envía a topic `orders_user_<user_id>`.

## Requisitos

Necesitas un **Service Account** de Firebase (JSON).

## Opción A (Recomendada): variable `FCM_SERVICE_ACCOUNT_JSON`

En tu hosting agrega en tu `.env` (o variables del panel):

- `FCM_SERVICE_ACCOUNT_JSON=` con TODO el JSON del service account (en una sola línea, escapado si tu panel lo requiere).

## Opción B: archivo JSON en el servidor

1. Sube el JSON a una ruta segura (ej: fuera de `public/`).
2. En `.env` configura:

- `FCM_SERVICE_ACCOUNT_PATH=/ruta/absoluta/al/service-account.json`

## Nota

Sin estas variables, el sistema seguirá funcionando, pero **no enviará push** (FCM) y solo tendrás avisos dentro de la app cuando esté abierta (Pusher / polling).

