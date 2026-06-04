# Build Android en Windows (Solución de errores comunes)

## 1) Activa Developer Mode (symlinks)
Flutter muestra:
> Building with plugins requires symlink support.

Activa **Developer Mode** en Windows:

- Ejecuta: `start ms-settings:developers`
- Enciende: **Developer Mode**
- Reinicia la PC (recomendado)

## 2) Error Gradle: `Could not move temporary workspace ... transforms`
Suele ser lock de archivos por antivirus/indexer/daemon.

Pasos recomendados:

```powershell
cd C:\Proyectos\WEB_POLLERIA\WEB_POLLERIA\AppMovilPollos\apppllr-main
flutter clean
cd android
.\gradlew --stop
Remove-Item -Recurse -Force "$env:USERPROFILE\.gradle\caches"
cd ..
flutter pub get
flutter run
```

Si aún falla:

- Cierra Android Studio y cualquier proceso `java.exe`/`gradle` que esté usando `.gradle`.
- Agrega exclusión del antivirus a: `C:\Users\<tu_usuario>\.gradle\`
- Alternativa rápida: usar un GRADLE_USER_HOME corto:

```powershell
$env:GRADLE_USER_HOME="C:\gradle_home"
flutter run
```

## 3) Error NDK: `[CXX1101] NDK ... did not have a source.properties file`
Esto pasa cuando la descarga del NDK quedo corrupta/incompleta.

Solucion rapida (PowerShell):

```powershell
# Cierra Android Studio antes de esto
$ndk="28.2.13676358"
$sdk="$env:LOCALAPPDATA\\Android\\Sdk"
Remove-Item -Recurse -Force \"$sdk\\ndk\\$ndk\" -ErrorAction SilentlyContinue

# Luego deja que Gradle/Android plugin lo re-descargue
cd AppMovilPollos\\apppllr-main
flutter clean
flutter build apk --release
```

Alternativa (Android Studio):
- `SDK Manager` -> `SDK Tools` -> `NDK (Side by side)` -> reinstalar la version que te pide Flutter.

## 4) Error: "Gradle build daemon disappeared" / `hs_err_pid*.log` (falta de RAM)
Si en `android/hs_err_pid*.log` aparece:
> There is insufficient memory for the Java Runtime Environment to continue

Entonces baja el consumo de memoria de Gradle.

En este proyecto ya se ajusto `android/gradle.properties` para PCs con poca RAM.

Luego ejecuta:

```powershell
cd AppMovilPollos\\apppllr-main
flutter clean
flutter build apk --release
```
