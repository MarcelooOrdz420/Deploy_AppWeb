# Generar y publicar el APK (AppMovilPollos)

## 1) Generar APK release (Flutter)

En Windows (PowerShell):

```powershell
cd AppMovilPollos\apppllr-main
flutter pub get
flutter build apk --release
```

Antes de probar en **celular físico**, configura la URL del backend (API):

- Dentro de la app: abre **Configurar servidor** y coloca la URL (ej: `http://192.168.1.10:8000` o tu dominio `https://api.tudominio.com`).
- Importante: en celular físico **NO** funciona `http://127.0.0.1:8000` (eso apunta al propio celular).

Salida esperada:

- `AppMovilPollos\apppllr-main\build\app\outputs\flutter-apk\app-release.apk`

Renombra a:

- `AppMovilPollos.apk`

## 2) Publicar el APK para descarga en la Web

Este repo ya tiene un endpoint de descarga:

- `GET /descargar-apk` (ruta `apk.download`)

Solo copia el archivo generado a:

- `laravel-app\public\downloads\AppMovilPollos.apk`

(En hosting: dentro del `public/` que apunte tu dominio.)
