<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <title> Iniciar Sesion </title>
    <style>
        :root {
            --orange: #ff6f1f;
            --orange-soft: #ff9d5a;
            --orange-deep: #f25d00;
            --cream: #fff8f2;
            --white: #ffffff;
            --text-dark: #24160f;
            --text-muted: #68432e;
            --line: rgba(255, 255, 255, 0.45);
            --field: #fff4eb;
            --shadow-card: 0 28px 60px rgba(53, 21, 0, 0.28);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255, 157, 90, 0.22), transparent 24%),
                radial-gradient(circle at bottom right, rgba(242, 93, 0, 0.16), transparent 24%),
                linear-gradient(135deg, #2b170c 0%, #18110d 42%, #0f0f10 100%);
        }

        .auth-shell {
            width: min(980px, 100%);
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: var(--cream);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: var(--shadow-card);
            min-height: 620px;
        }

        .form-panel {
            background: rgba(255, 255, 255, 0.96);
            padding: 48px 54px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .slider-panel {
            position: relative;
            padding: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 26%),
                linear-gradient(145deg, var(--orange) 0%, var(--orange-soft) 55%, var(--orange-deep) 100%);
            color: #fff6ef;
            isolation: isolate;
        }

        .slider-panel::before,
        .slider-panel::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            z-index: 0;
        }

        .slider-panel::before {
            width: 280px;
            height: 280px;
            top: -90px;
            right: -70px;
        }

        .slider-panel::after {
            width: 220px;
            height: 220px;
            bottom: -90px;
            left: -60px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #8a4718;
            text-transform: uppercase;
        }

        .brand-icon {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0 0 7px rgba(255, 111, 31, 0.12);
        }

        h1 {
            margin: 0 0 10px;
            color: var(--text-dark);
            font-size: clamp(32px, 5vw, 44px);
            line-height: 1;
        }

        .lead {
            margin: 0 0 26px;
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #5e2f10;
            font-size: 13px;
            font-weight: 800;
        }

        input {
            width: 100%;
            border: 1px solid #f0ccb0;
            border-radius: 14px;
            padding: 14px 15px;
            margin-bottom: 16px;
            background: var(--field);
            color: var(--text-dark);
            font-size: 15px;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--orange);
            box-shadow: 0 0 0 4px rgba(255, 111, 31, 0.14);
            transform: translateY(-1px);
        }

        button {
            width: 100%;
            border: 0;
            border-radius: 999px;
            padding: 14px 18px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 900;
            color: #3b1f11;
            background: linear-gradient(120deg, var(--orange), var(--orange-soft));
            box-shadow: 0 12px 24px rgba(255, 111, 31, 0.26);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(255, 111, 31, 0.32);
        }

        .msg {
            min-height: 22px;
            margin-top: 14px;
            color: #9d460d;
            font-size: 14px;
            font-weight: 700;
        }

        .footer-links {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 14px;
        }

        .footer-links a,
        .switch-link {
            color: #8a3f0a;
            font-weight: 800;
            text-decoration: none;
        }

        .footer-links a:hover,
        .switch-link:hover {
            color: var(--orange-deep);
        }

        .slider-content {
            position: relative;
            z-index: 1;
            max-width: 320px;
            text-align: center;
            animation: slideInRight .7s ease;
        }

        .slider-kicker {
            margin: 0 0 10px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 247, 238, 0.88);
        }

        .slider-content h2 {
            margin: 0 0 12px;
            font-size: clamp(30px, 5vw, 42px);
            line-height: 1.05;
        }

        .slider-content p {
            margin: 0 0 28px;
            font-size: 16px;
            line-height: 1.6;
            color: #fff0dd;
        }

        .ghost-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 170px;
            padding: 13px 18px;
            border-radius: 999px;
            border: 2px solid var(--line);
            color: #fffaf5;
            text-decoration: none;
            font-weight: 900;
            transition: transform .2s ease, background .2s ease, border-color .2s ease;
        }

        .ghost-btn:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.7);
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(28px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 860px) {
            .auth-shell {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .slider-panel {
                order: -1;
                min-height: 260px;
                padding: 34px 26px;
            }

            .form-panel {
                padding: 34px 24px;
            }
        }
    </style>
</head>
<body>
<main class="auth-shell">
    <section class="form-panel">
        <div class="brand">
            <img src="/images/ico-pollo.jpg" alt="Pollos y Parrillas El Dorado" class="brand-icon">
            Pollos y Parrillas El Dorado
        </div>

        <h1>Inicia Sesión</h1>
        <p class="lead">Inicia sesión para comprar, revisar tus pedidos y seguir el estado de tus compras.</p>

        <form id="loginForm">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" placeholder="xxxxxxx@gmail.com" required>

            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" placeholder="Ingresa tu contraseña" required>

            <button type="submit">Iniciar Sesión</button>
        </form>

        <div id="msg" class="msg"></div>

        <div class="footer-links">
            <a href="/productos">Ir a la tienda</a>
            <a href="/register">Crear cuenta</a>
        </div>
    </section>

    <aside class="slider-panel">
        <div class="slider-content">
            <p class="slider-kicker">Bienvenido!!</p>
            <h2>¿No tienes cuenta?</h2>
            <p>Crea tu cuenta y entra a una experiencia mas rapida para comprar y seguir tus pedidos.</p>
            <h3>Registrate como cliente por aqui👇</h3>
            <a class="ghost-btn" href="/register">Crear Cuenta</a>
        </div>
    </aside>
</main>

<script>
(() => {
    const apiBase = @json(config('app.api_base_url'));
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

const form = document.getElementById('loginForm');
const msg = document.getElementById('msg');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.textContent = 'Ingresando...';

    const payload = { email: form.email.value.trim(), password: form.password.value };

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

        localStorage.setItem('ed_token', data.token);
        localStorage.setItem('ed_user', JSON.stringify(data.user));
        localStorage.setItem('ed_session', JSON.stringify({
            role: data.user.role || 'customer',
            lastActivity: Date.now(),
            expiresAt: Date.now() + (60 * 60 * 1000),
        }));
        window.location.href = data.user && data.user.role === 'admin' ? '/admin/panel' : '/productos';
    } catch {
        msg.textContent = 'No se pudo conectar con el servidor.';
    }
});
</script>
</body>
</html>
