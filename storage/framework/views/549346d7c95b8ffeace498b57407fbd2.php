<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <title>Pollos y Parrillas El Dorado - Login Admin</title>
    <style>
        :root {
            --orange: #ff6f1f;
            --orange-soft: #ff9f62;
            --paper: #fffdf9;
            --paper-soft: #fff3e6;
            --ink: #2b190f;
            --ink-soft: #7b4a2a;
            --line: #f0cfb3;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(255, 159, 98, .22), transparent 28%),
                radial-gradient(circle at bottom right, rgba(255, 111, 31, .18), transparent 24%),
                linear-gradient(180deg, #fff8f2 0%, #fff1e5 48%, #ffe7d3 100%);
        }

        .login-shell {
            width: min(980px, 100%);
            display: grid;
            grid-template-columns: .98fr .86fr;
            border-radius: 34px;
            overflow: hidden;
            border: 1px solid rgba(240, 207, 179, .92);
            background: rgba(255, 252, 248, .94);
            box-shadow: 0 32px 64px rgba(52, 17, 0, .14);
        }

        .login-story,
        .login-card {
            padding: clamp(24px, 4vw, 38px);
        }

        .login-story {
            display: grid;
            align-content: space-between;
            gap: 22px;
            background:
                linear-gradient(160deg, rgba(255,255,255,.92), rgba(255,244,233,.92)),
                radial-gradient(circle at top right, rgba(255, 111, 31, .10), transparent 24%);
            border-right: 1px solid rgba(240, 207, 179, .88);
        }

        .eyebrow {
            margin: 0;
            font-size: 11px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #9b5a2c;
            font-weight: 900;
        }

        h1 {
            margin: 0;
            font-size: clamp(34px, 4vw, 54px);
            line-height: .95;
            color: #2d1708;
        }

        .lead {
            margin: 0;
            color: var(--ink-soft);
            font-size: 15px;
            line-height: 1.7;
            max-width: 420px;
        }

        .story-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .story-badges span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(234, 182, 138, .82);
            background: rgba(255, 247, 240, .88);
            color: #82471f;
            font-size: 12px;
            font-weight: 900;
        }

        .login-card {
            display: grid;
            gap: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,247,239,.94));
        }

        .card-head h2 {
            margin: 0 0 6px;
            font-size: 30px;
            color: #8d3d00;
        }

        .card-head p {
            margin: 0;
            color: var(--ink-soft);
            font-size: 14px;
            line-height: 1.6;
        }

        .field-grid {
            display: grid;
            gap: 14px;
        }

        label {
            display: grid;
            gap: 8px;
            font-size: 13px;
            font-weight: 800;
            color: #64320f;
        }

        input {
            width: 100%;
            border: 1px solid #edc8a8;
            border-radius: 16px;
            background: #fffdfb;
            color: var(--ink);
            padding: 13px 14px;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        input:focus {
            outline: none;
            border-color: #ffb173;
            box-shadow: 0 0 0 4px rgba(255, 111, 31, .10);
            transform: translateY(-1px);
        }

        button {
            width: 100%;
            border: 0;
            border-radius: 16px;
            padding: 14px;
            cursor: pointer;
            font-weight: 900;
            font-size: 15px;
            color: #2e1608;
            background: linear-gradient(120deg, var(--orange), var(--orange-soft));
            box-shadow: 0 16px 28px rgba(255, 111, 31, .16);
        }

        .msg {
            min-height: 22px;
            color: #8b4304;
            font-size: 13px;
            line-height: 1.5;
        }

        .back-link {
            color: #8d3d00;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
        }

        @media (max-width: 860px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-story {
                border-right: 0;
                border-bottom: 1px solid rgba(240, 207, 179, .88);
            }
        }
    </style>
</head>
<body>
<main class="login-shell">
    <section class="login-story">
        <div>
            <p class="eyebrow">Acceso Interno</p>
            <h1>Administra cocina, ventas y pedidos desde un solo frente.</h1>
        </div>

        <p class="lead">
            Entra al panel para revisar productos, stock interno, ventas y validacion de pagos sin perder visibilidad operativa del negocio.
        </p>

        <div class="story-badges">
            <span>Stock interno</span>
            <span>Ventas historicas</span>
            <span>Pagos digitales</span>
        </div>
    </section>

    <section class="login-card">
        <div class="card-head">
            <p class="eyebrow">Login Admin</p>
            <h2>Ingresa al panel</h2>
            <p>Solo usuarios con rol administrador pueden entrar a esta zona.</p>
        </div>

        <form id="adminLoginForm" class="field-grid">
            <label for="email">Correo
                <input id="email" name="email" type="email" required>
            </label>

            <label for="password">Contrasena
                <input id="password" name="password" type="password" required>
            </label>

            <button type="submit">Ingresar al panel</button>
        </form>

        <div id="msg" class="msg"></div>

        <a href="/productos" class="back-link">Volver al sitio</a>
    </section>
</main>

<script>
(() => {
    const apiBase = <?php echo json_encode(config('app.api_base_url'), 15, 512) ?>;
    const base = (apiBase || '').toString().replace(/\/+$/, '');

    const originalFetch = window.fetch.bind(window);
    window.fetch = (input, init) => {
        if (typeof input === 'string' && input.startsWith('/api/')) {
            if (base) input = `${base}${input}`;
            const headers = new Headers((init && init.headers) ? init.headers : undefined);
            headers.set('Accept', 'application/json');
            init = { ...(init || {}), headers };
        }
        return originalFetch(input, init);
    };
})();

const form = document.getElementById('adminLoginForm');
const msg = document.getElementById('msg');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.textContent = 'Validando...';

    const payload = {
        email: form.email.value.trim(),
        password: form.password.value,
    };

    try {
        const res = await fetch('/api/v1/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (!res.ok) {
            msg.textContent = data.message || 'Credenciales invalidas';
            return;
        }

        if (!data.user || data.user.role !== 'admin') {
            msg.textContent = 'Este usuario no tiene permisos de administrador.';
            return;
        }

        localStorage.removeItem('ed_cart');
        localStorage.removeItem('ed_last_tracking');
        localStorage.removeItem('ed_recent_trackings');
        localStorage.setItem('ed_token', data.token);
        localStorage.setItem('ed_user', JSON.stringify(data.user));
        localStorage.setItem('ed_session', JSON.stringify({
            role: 'admin',
            lastActivity: Date.now(),
            expiresAt: Date.now() + (30 * 60 * 1000),
        }));
        window.location.href = '/admin/panel';
    } catch {
        msg.textContent = 'Error de conexion.';
    }
});
</script>
</body>
</html>
<?php /**PATH C:\Proyectos\WEB_POLLERIA\laravel-app\resources\views\admin\login.blade.php ENDPATH**/ ?>