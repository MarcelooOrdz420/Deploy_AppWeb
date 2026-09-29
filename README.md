# 🍗 Pollos y Parrillas El Dorado — Sistema Web y App Móvil

Sistema de pedidos en línea desarrollado para **Pollos y Parrillas El Dorado**, una empresa real de Huancayo, Perú. Incluye una tienda web, una aplicación móvil en Flutter, un panel de administración y una app para repartidores, todo conectado a una API REST en Laravel.

Proyecto de titulación de la carrera de **Desarrollo de Sistemas de Información** (Instituto Continental), desarrollado entre **julio 2025 y agosto 2026** con asesoría docente.

🌐 **Sitio en producción:** https://pollos.saborcentral.com

---

## 📸 Capturas

| Tienda web | App móvil | Panel de administración |
|---|---|---|
| ![Tienda web](docs/capturas/tienda.png) | ![App móvil](docs/capturas/app.png) | ![Panel admin](docs/capturas/admin.png) |

---

## ✨ Funcionalidades principales

**Clientes**
- Catálogo de productos, carrito de compras y promociones activas.
- Registro e inicio de sesión (correo con verificación OTP o cuenta de Google).
- Pagos en línea con **Izipay** (tarjetas, Yape y Plin).
- Seguimiento de pedidos en tiempo real y descarga de comprobantes.
- Asistente virtual de atención al cliente.

**Repartidores**
- Lista de pedidos disponibles, asignación y actualización del estado de entrega.

**Administración**
- Gestión de productos, promociones, usuarios y ofertas laborales.
- Cierre de caja y perfil de la empresa.
- Envío de notificaciones push de ofertas y campañas de recuperación de carritos.
- Emisión de **facturación electrónica** mediante Nubefact.

---

## 🛠️ Tecnologías

| Capa | Tecnologías |
|---|---|
| Backend | PHP 8.2, Laravel 12, API REST |
| Base de datos | MySQL |
| Frontend web | Blade, Tailwind CSS, Vite |
| App móvil | Flutter (Dart), Dio, GoRouter |
| Tiempo real | Pusher |
| Notificaciones | Firebase Cloud Messaging |
| Pagos | Izipay (vía Cloudflare Worker) |
| Facturación | Nubefact |
| Pruebas | PHPUnit |
| Despliegue | GitHub Actions (CI/CD) |

---

## 🏗️ Arquitectura

```
┌────────────┐   ┌────────────┐   ┌──────────────┐
│ Tienda web │   │ App Flutter│   │ Panel admin  │
└─────┬──────┘   └─────┬──────┘   └──────┬───────┘
      └────────────────┼─────────────────┘
                       ▼
              API REST (Laravel 12)
                       │
     ┌──────────┬──────┴─────┬───────────┬──────────┐
     ▼          ▼            ▼           ▼          ▼
   MySQL     Pusher     Firebase FCM   Izipay    Nubefact
                                  (Cloudflare Worker)
```

---

## 📂 Estructura del repositorio

```
app/                         Lógica del backend (controladores, modelos, servicios)
routes/                      Rutas web y API (api/v1)
database/                    Migraciones y seeders
resources/                   Vistas Blade y assets del frontend
AppMovilPollos/apppllr-main/ Aplicación móvil en Flutter
cloudflare-worker/           Relay de notificaciones de pago de Izipay
.github/workflows/           Pipeline de despliegue (CI/CD)
tests/                       Pruebas automatizadas
```

---

## 🚀 Instalación local

**Requisitos:** PHP 8.2+, Composer, Node.js, MySQL.

```bash
git clone https://github.com/MarcelooOrdz420/Deploy_AppWeb.git
cd Deploy_AppWeb
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configurar la conexión a MySQL en .env
php artisan migrate --seed
npm run dev
php artisan serve
```

Guías de configuración de cada integración:
- [Izipay](IZIPAY_SETUP.md)
- [Firebase Cloud Messaging](FCM_SETUP.md)
- [Nubefact](NUBEFACT_POSTMAN.md)
- [Asistente virtual](OLLAMA_SETUP.md)
- [Variables de producción](HOSTING_ENV_CHECKLIST.md)

**App móvil:**
```bash
cd AppMovilPollos/apppllr-main
flutter pub get
flutter run
```

---

## 👤 Autor

**Carlo Marcelo Ordoñez Arauco** — Desarrollador Full Stack Junior
[LinkedIn](https://www.linkedin.com/in/carlo-marcelo-ordo%C3%B1ez-arauco-61895230b/) · camarord2002@gmail.com
