# Despliegue en Coolify

Este proyecto ya incluye `Dockerfile`, `unit.json` y el script `docker/00-app-setup.sh` para desplegarse en Coolify usando el build pack `Dockerfile`.

## Configuracion en Coolify

1. Crea un nuevo recurso desde tu repositorio de GitHub.
2. Selecciona `Dockerfile` como `Build Pack`.
3. Define `8000` en `Ports Exposes`.
4. Configura las variables de entorno tomando como base [`.env.production.example`](/c:/Users/LABF/Downloads/Deploy_AppWeb/.env.production.example).

## Variables minimas recomendadas

```env
APP_NAME="Pollos y Parrillas El Dorado"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
APP_KEY=

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

## Comando Post-deployment

Usa este comando en el campo `Post-deployment` de Coolify:

```sh
php artisan migrate --force
```

Si prefieres incluir optimizacion despues del deploy:

```sh
php artisan migrate --force && php artisan optimize
```

## Notas importantes

- El contenedor ya crea `public/storage`, limpia caches antiguas y ejecuta `php artisan optimize` al arrancar.
- Se habilito `trustProxies('*')` en Laravel para que Coolify no rompa HTTPS, IP real ni URLs generadas detras del proxy.
- El proyecto actualmente no usa `@vite` ni tiene `vite.config.*`, por eso el `Dockerfile` no ejecuta `npm build`.
