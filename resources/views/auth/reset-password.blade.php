<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <title>Recuperar contrasena</title>
    <style>
        :root {
            --orange: #ff6f1f;
            --orange-soft: #ff9d5a;
            --orange-deep: #f25d00;
            --cream: #fff8f2;
            --white: #ffffff;
            --text-dark: #24160f;
            --text-muted: #68432e;
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
        .card {
            width: min(520px, 100%);
            padding: 34px;
            border-radius: 26px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 28px 60px rgba(53, 21, 0, 0.28);
        }
        h1 { margin: 0 0 10px; color: var(--text-dark); font-size: 34px; }
        p { margin: 0 0 20px; color: var(--text-muted); line-height: 1.6; }
        label { display: block; margin-bottom: 8px; color: #5e2f10; font-size: 13px; font-weight: 800; }
        input {
            width: 100%;
            border: 1px solid #f0ccb0;
            border-radius: 14px;
            padding: 14px 15px;
            margin-bottom: 16px;
            background: #fff4eb;
            color: var(--text-dark);
            font-size: 15px;
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
        }
        .msg { min-height: 22px; margin-top: 14px; color: #9d460d; font-size: 14px; font-weight: 700; }
        .warn {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 16px;
            background: #fff6ee;
            border: 1px solid #f6d7c2;
            color: #7b3f15;
            font-size: 14px;
            line-height: 1.55;
            display: none;
        }
        a { color: #8a3f0a; font-weight: 800; text-decoration: none; }
    </style>
</head>
<body>
<main class="card">
    <h1>Nueva contrasena</h1>
    <p>Escribe tu nueva clave para volver a entrar a tu cuenta.</p>

    <form id="resetPasswordForm">
        <label for="email">Correo</label>
        <input id="email" name="email" type="email" value="{{ request('email') }}" required>

        <label for="password">Nueva contrasena</label>
        <input id="password" name="password" type="password" minlength="6" required>

        <label for="password_confirmation">Confirmar contrasena</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="6" required>

        <input type="hidden" id="token" name="token" value="{{ request('token') }}">

        <button type="submit">Guardar nueva contrasena</button>
    </form>

    <div id="msg" class="msg"></div>
    <div id="resetWarn" class="warn"></div>
    <p style="margin-top:18px;"><a href="/login">Volver al login</a></p>
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

const form = document.getElementById('resetPasswordForm');
const msg = document.getElementById('msg');
const resetWarn = document.getElementById('resetWarn');
const RESET_PENDING_KEY = 'ed_password_reset_pending_v1';
const RESET_CHANNEL = 'BroadcastChannel' in window ? new BroadcastChannel('ed-password-reset') : null;
const token = form.token.value.trim();
const lockKey = `ed_reset_token_lock_${btoa(token).replace(/=+$/,'')}`;
const tabId = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
let lockHeartbeat = null;

function readLock() {
    try { return JSON.parse(localStorage.getItem(lockKey) || 'null'); } catch { return null; }
}

function writeLock() {
    localStorage.setItem(lockKey, JSON.stringify({ owner: tabId, touchedAt: Date.now() }));
}

function clearLock() {
    const current = readLock();
    if (current?.owner === tabId) {
        localStorage.removeItem(lockKey);
    }
}

function setBlocked(message) {
    resetWarn.style.display = 'block';
    resetWarn.textContent = message;
    Array.from(form.elements).forEach((field) => field.disabled = true);
}

function startLock() {
    const current = readLock();
    if (current && current.owner !== tabId && Date.now() - Number(current.touchedAt || 0) < 90000) {
        setBlocked('Este cambio de contrasena ya esta abierto en otra pestana. Termina el proceso ahi para evitar errores.');
        return false;
    }

    writeLock();
    lockHeartbeat = setInterval(writeLock, 30000);
    return true;
}

function clearResetPending() {
    localStorage.removeItem(RESET_PENDING_KEY);
    RESET_CHANNEL?.postMessage({ type: 'cleared' });
}

window.addEventListener('beforeunload', () => {
    if (lockHeartbeat) clearInterval(lockHeartbeat);
    clearLock();
});

if (!startLock()) {
    msg.textContent = '';
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    msg.textContent = 'Actualizando contrasena...';

    try {
        const res = await fetch('/api/v1/auth/reset-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                email: form.email.value.trim(),
                token: form.token.value.trim(),
                password: form.password.value,
                password_confirmation: form.password_confirmation.value,
            }),
        });

        const data = await res.json();

        if (!res.ok) {
            msg.textContent = data.message || 'No se pudo actualizar la contrasena.';
            if (String(data.message || '').toLowerCase().includes('no es valido') || String(data.message || '').toLowerCase().includes('vencio')) {
                clearResetPending();
                clearLock();
            }
            return;
        }

        msg.style.color = '#166534';
        msg.textContent = data.message || 'Contrasena actualizada correctamente.';
        clearResetPending();
        clearLock();
        setTimeout(() => window.location.href = '/login', 1200);
    } catch {
        msg.textContent = 'No se pudo conectar con el servidor.';
    }
});
</script>
</body>
</html>
