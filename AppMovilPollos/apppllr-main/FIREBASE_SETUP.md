# Firebase (FCM) - Configuración para AppMovilPollos

Esta app ya tiene código para recibir notificaciones (FCM) en `lib/services/push_notifications_service.dart`, pero **Firebase debe configurarse** para que funcionen en un celular real y fuera de local.

## 1) Crear proyecto en Firebase

1. Entra a Firebase Console y crea un proyecto.
2. Activa **Cloud Messaging** (FCM).

## 2) Registrar la app Android

1. Agrega una app Android en Firebase.
2. Usa como **Android package name** el `applicationId` de tu app (revisa `android/app/build.gradle` o `AndroidManifest.xml`).
3. Descarga el archivo `google-services.json`.

## 3) Copiar `google-services.json`

Colócalo en:

`AppMovilPollos/apppllr-main/android/app/google-services.json`

## 4) Verificar Gradle

En general, Flutter/Firebase requieren:

- Plugin de Google Services aplicado en `android/app/build.gradle(.kts)`
- Dependencias de `firebase_core` y `firebase_messaging` en `pubspec.yaml`

## 5) Probar en dispositivo

1. Recompila:
   - `flutter clean`
   - `flutter build apk --release`
2. Instala el APK y acepta permisos de notificaciones.

## 6) Topics para pedidos

La app se suscribe automáticamente a:

- `promo_all` y `promo_mobile`
- `orders_user_<ID>` cuando el usuario inicia sesión (para avisos de estado del pedido).

El backend envía notificaciones a ese topic cuando el admin cambia el estado del pedido.

