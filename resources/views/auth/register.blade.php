<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <title>Pollos y Parrillas El Dorado - Registro</title>
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
                radial-gradient(circle at top right, rgba(255, 157, 90, 0.22), transparent 24%),
                radial-gradient(circle at bottom left, rgba(242, 93, 0, 0.16), transparent 24%),
                linear-gradient(225deg, #2b170c 0%, #18110d 42%, #0f0f10 100%);
        }

        .auth-shell {
            width: min(1040px, 100%);
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: var(--cream);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: var(--shadow-card);
            min-height: 640px;
        }

        .slider-panel {
            position: relative;
            padding: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 26%),
                linear-gradient(145deg, var(--orange-deep) 0%, var(--orange) 45%, var(--orange-soft) 100%);
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
            width: 300px;
            height: 300px;
            top: -110px;
            left: -80px;
        }

        .slider-panel::after {
            width: 220px;
            height: 220px;
            bottom: -90px;
            right: -60px;
        }

        .form-panel {
            background: rgba(255, 255, 255, 0.97);
            padding: 46px 54px;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
            margin: 0 0 24px;
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
            margin-bottom: 14px;
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

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 16px 0 6px;
            color: #9a6f57;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #efd2bd;
        }

        .google-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 8px;
            padding: 14px 18px;
            border-radius: 999px;
            border: 1px solid #ead0bc;
            background: #fff;
            color: #24160f;
            cursor: pointer;
            font-size: 15px;
            font-weight: 900;
            box-shadow: 0 12px 24px rgba(36, 22, 15, 0.08);
        }

        .google-btn:hover {
            transform: translateY(-1px);
        }

        .msg {
            min-height: 22px;
            margin-top: 14px;
            color: #9d460d;
            font-size: 14px;
            font-weight: 700;
        }

        .otp-panel {
            margin-top: 22px;
            padding: 20px;
            border: 1px solid #f0ccb0;
            border-radius: 18px;
            background: #fff4eb;
        }

        .otp-panel[hidden] {
            display: none;
        }

        .otp-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
            margin: 16px 0;
        }

        .otp-grid input {
            margin-bottom: 0;
            padding: 12px;
            text-align: center;
            font-size: 22px;
            font-weight: 800;
        }

        .otp-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 14px;
        }

        .otp-link-btn {
            width: auto;
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
            color: #8a3f0a;
            font-size: 14px;
            font-weight: 800;
        }

        .otp-link-btn:hover {
            transform: none;
            box-shadow: none;
            color: var(--orange-deep);
        }

        .otp-note {
            margin: 0;
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .footer-links {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 14px;
        }

        .footer-links a {
            color: #8a3f0a;
            font-weight: 800;
            text-decoration: none;
        }

        .footer-links a:hover,
        .ghost-btn:hover {
            color: var(--orange-deep);
        }

        .slider-content {
            position: relative;
            z-index: 1;
            max-width: 320px;
            text-align: center;
            animation: slideInLeft .7s ease;
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

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-28px);
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
    <aside class="slider-panel">
        <div class="slider-content">
            <p class="slider-kicker">¿Ya tienes cuenta?</p>
            <p>Inicia sesión para continuar con tus compras, revisar pedidos.</p>
            <h3>Inicia sesión por aqui👇</h3>
            <a class="ghost-btn" href="/login">Iniciar Sesión</a>
        </div>
    </aside>

    <section class="form-panel">
        <div class="brand">
            <img src="/images/ico-pollo.jpg" alt="Pollos y Parrillas El Dorado" class="brand-icon">
            Pollos y Parrillas El Dorado
        </div>

        <h1>Crear Cuenta</h1>
        <p class="lead">Registra tu cuenta para comprar mas rapido y guardar tu acceso dentro de la tienda.</p>

        <form id="registerForm">
            <label for="name">Nombre</label>
            <input id="name" name="name" type="text" placeholder="Tu nombre completo" required>

            <label for="email">Correo</label>
            <input id="email" name="email" type="email" placeholder="xxxxx@gmail.com" required>

            <label for="phone">Teléfono</label>
            <input id="phone" name="phone" type="text" placeholder="Telefono opcional">

            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" placeholder="Minimo 6 caracteres" required minlength="6">

            <label style="display:flex; align-items:flex-start; gap:10px; margin:0 0 16px; font-weight:700; color:#6a3a1a;">
                <input id="marketingEmailsEnabled" name="marketing_emails_enabled" type="checkbox" checked style="width:auto; margin:2px 0 0;">
                <span>Deseo recibir promociones y recordatorios por correo.</span>
            </label>

            <button type="submit">Crear Cuenta</button>
        </form>

        @if (config('services.google_auth.web_client_id'))
            <div class="divider">o continua con</div>
            <div id="googleRegisterBtn"></div>
        @endif

        <div id="msg" class="msg"></div>

        <section id="otpPanel" class="otp-panel" hidden>
            <h3 style="margin:0 0 8px;color:#24160f;">Verifica tu correo</h3>
            <p class="otp-note">Te enviamos un codigo de 6 digitos a <strong id="otpEmailLabel"></strong>. La cuenta se activara cuando el codigo sea correcto.</p>

            <form id="otpForm">
                <div class="otp-grid">
                    <input type="text" inputmode="numeric" maxlength="1" data-otp-input required>
                    <input type="text" inputmode="numeric" maxlength="1" data-otp-input required>
                    <input type="text" inputmode="numeric" maxlength="1" data-otp-input required>
                    <input type="text" inputmode="numeric" maxlength="1" data-otp-input required>
                    <input type="text" inputmode="numeric" maxlength="1" data-otp-input required>
                    <input type="text" inputmode="numeric" maxlength="1" data-otp-input required>
                </div>

                <button type="submit">Verificar Codigo</button>
            </form>

            <div class="otp-actions">
                <button type="button" id="resendOtpBtn" class="otp-link-btn">Reenviar codigo</button>
                <span id="otpCooldown" class="otp-note"></span>
            </div>

            <div id="otpMsg" class="msg" style="margin-top:12px;"></div>
        </section>

        <div class="footer-links">
            <a href="/productos">Ir a la tienda</a>
            <a href="/login">Ya tengo cuenta</a>
        </div>
    </section>
</main>

@if (config('services.google_auth.web_client_id'))
<script src="https://accounts.google.com/gsi/client" async defer></script>
@endif
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

const form = document.getElementById('registerForm');
const msg = document.getElementById('msg');
const otpPanel = document.getElementById('otpPanel');
const otpForm = document.getElementById('otpForm');
const otpMsg = document.getElementById('otpMsg');
const otpEmailLabel = document.getElementById('otpEmailLabel');
const resendOtpBtn = document.getElementById('resendOtpBtn');
const otpCooldown = document.getElementById('otpCooldown');
const otpInputs = Array.from(document.querySelectorAll('[data-otp-input]'));
const googleRegisterBtn = document.getElementById('googleRegisterBtn');
let pendingEmail = '';
let cooldownTimer = null;
let cooldown = 0;

function extractRegisterError(data) {
    const firstError = Object.values(data?.errors || {})[0]?.[0];
    return firstError || data?.message || 'No se pudo registrar.';
}

function setSessionFromAuth(data) {
    localStorage.setItem('ed_token', data.token);
    localStorage.setItem('ed_user', JSON.stringify(data.user));
    localStorage.setItem('ed_session', JSON.stringify({
        role: data.user.role || 'customer',
        lastActivity: Date.now(),
        expiresAt: Date.now() + (60 * 60 * 1000),
    }));
}

function setOtpMessage(text, ok = false) {
    otpMsg.textContent = text;
    otpMsg.style.color = ok ? '#166534' : '#9d460d';
}

function startOtpCooldown(seconds = 60) {
    cooldown = seconds;
    resendOtpBtn.disabled = true;
    clearInterval(cooldownTimer);

    const render = () => {
        otpCooldown.textContent = cooldown > 0 ? `Reenviar en ${cooldown}s` : '';
        resendOtpBtn.disabled = cooldown > 0;
    };

    render();
    cooldownTimer = setInterval(() => {
        cooldown -= 1;
        if (cooldown <= 0) {
            clearInterval(cooldownTimer);
            cooldown = 0;
        }
        render();
    }, 1000);
}

function revealOtpPanel(email) {
    pendingEmail = email;
    otpEmailLabel.textContent = email;
    otpPanel.hidden = false;
    form.querySelector('button[type="submit"]').disabled = true;
    otpInputs.forEach((input) => {
        input.value = '';
    });
    otpInputs[0]?.focus();
    startOtpCooldown(60);
}

otpInputs.forEach((input, index) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 1);
        if (input.value && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Backspace' && !input.value && index > 0) {
            otpInputs[index - 1].focus();
        }
    });
});

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.textContent = 'Creando cuenta...';

    const payload = {
        name: form.name.value.trim(),
        email: form.email.value.trim(),
        phone: form.phone.value.trim() || null,
        password: form.password.value,
        marketing_emails_enabled: form.marketing_emails_enabled.checked,
    };

    try {
        const res = await fetch('/api/v1/auth/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (!res.ok) {
            msg.textContent = extractRegisterError(data);
            return;
        }

        msg.textContent = data.message || 'Revisa tu correo y completa el codigo OTP.';
        revealOtpPanel(payload.email);
    } catch {
        msg.textContent = 'No se pudo conectar con el servidor.';
    }
});

otpForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const code = otpInputs.map((input) => input.value).join('');

    if (code.length !== 6 || !pendingEmail) {
        setOtpMessage('Completa los 6 digitos del codigo.');
        return;
    }

    setOtpMessage('Verificando codigo...');

    try {
        const res = await fetch('/api/v1/auth/verify-otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: pendingEmail, code }),
        });

        const data = await res.json();

        if (!res.ok) {
            setOtpMessage(data.message || 'No se pudo verificar el codigo.');
            return;
        }

        setSessionFromAuth(data);
        setOtpMessage(data.message || 'Correo verificado correctamente.', true);
        window.location.href = '/productos';
    } catch {
        setOtpMessage('No se pudo conectar con el servidor.');
    }
});

resendOtpBtn.addEventListener('click', async () => {
    if (!pendingEmail) {
        setOtpMessage('Primero registra la cuenta.');
        return;
    }

    setOtpMessage('Reenviando codigo...');

    try {
        const res = await fetch('/api/v1/auth/resend-otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: pendingEmail }),
        });

        const data = await res.json();

        if (!res.ok) {
            setOtpMessage(data.message || 'No se pudo reenviar el codigo.');
            return;
        }

        setOtpMessage(data.message || 'Codigo reenviado correctamente.', true);
        startOtpCooldown(60);
    } catch {
        setOtpMessage('No se pudo conectar con el servidor.');
    }
});

@if (config('services.google_auth.web_client_id'))
window.handleGoogleRegister = async (response) => {
    msg.textContent = 'Validando cuenta de Google...';

    try {
        const res = await fetch('/api/v1/auth/google', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_token: response.credential }),
        });

        const data = await res.json();

        if (!res.ok) {
            msg.textContent = data.message || 'No se pudo registrar con Google.';
            return;
        }

        setSessionFromAuth(data);
        window.location.href = '/productos';
    } catch {
        msg.textContent = 'No se pudo conectar con el servidor.';
    }
};

window.addEventListener('load', () => {
    if (!window.google || !googleRegisterBtn) return;

    google.accounts.id.initialize({
        client_id: @json(config('services.google_auth.web_client_id')),
        callback: window.handleGoogleRegister,
        auto_select: false,
        cancel_on_tap_outside: true,
    });

    google.accounts.id.renderButton(googleRegisterBtn, {
        theme: 'outline',
        size: 'large',
        shape: 'pill',
        text: 'signup_with',
        width: googleRegisterBtn.offsetWidth || 320,
    });
});
@endif
</script>
</body>
</html>
