<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <title>@yield('title', 'Pollos y Parrillas El Dorado')</title>
    <style>
        :root {
            --orange: #ff7a1a;
            --orange-soft: #ff9a3d;
            --orange-deep: #ff5b00;
            --cream: #141414;
            --cream-strong: #1d1d1d;
            --paper: #181818;
            --paper-soft: #202020;
            --ink: #f7e3cf;
            --ink-soft: #dfbf9f;
            --accent-ink: #3b1f11;
            --panel-ink: #ffc98d;
            --muted-ink: #e7c9aa;
            --line: rgba(255, 122, 26, .24);
            --line-strong: rgba(255, 154, 61, .42);
            --shadow-soft: 0 18px 40px rgba(0, 0, 0, .26);
            --shadow-strong: 0 26px 60px rgba(0, 0, 0, .34);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 16px;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(255, 122, 26, .18), transparent 22%),
                radial-gradient(circle at top right, rgba(255, 154, 61, .14), transparent 24%),
                linear-gradient(180deg, #080808 0%, #101010 54%, #171717 100%);
        }

        a { color: inherit; }

        .store-shell {
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: min(1200px, 100%);
            margin: 0 auto;
        }

        .store-frame {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(234, 182, 138, .75);
            border-radius: 34px;
            background:
                linear-gradient(180deg, rgba(17,17,17,.96) 0%, rgba(24,24,24,.98) 100%);
            box-shadow: var(--shadow-strong);
            backdrop-filter: blur(12px);
        }

        .store-frame::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 14% 10%, rgba(255, 111, 31, .08), transparent 20%),
                radial-gradient(circle at 90% 4%, rgba(255, 157, 90, .10), transparent 18%);
            pointer-events: none;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255, 122, 26, .18);
            background: rgba(11, 11, 11, .84);
            backdrop-filter: blur(12px);
        }

        .topbar-inner {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 16px;
            align-items: center;
        }

        .brand-cluster {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .brand-mark {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            border: 1px solid rgba(255, 122, 26, .28);
            background:
                linear-gradient(145deg, rgba(32,32,32,.94), rgba(18,18,18,.98));
            box-shadow: 0 12px 24px rgba(0, 0, 0, .26);
            padding: 8px;
            flex-shrink: 0;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-copy {
            min-width: 0;
        }

        .brand-kicker {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .22em;
            text-transform: uppercase;
            color: var(--orange-soft);
        }

        .brand-title {
            margin: 0;
            font-size: clamp(24px, 3vw, 36px);
            line-height: .98;
            color: var(--panel-ink);
        }

        .brand-subtitle {
            margin: 7px 0 0;
            max-width: 440px;
            color: var(--muted-ink);
            font-size: 13px;
            line-height: 1.5;
        }

        .topbar-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-end;
        }

        .session-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .user-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid rgba(255, 122, 26, .22);
            background: rgba(27, 27, 27, .9);
            color: var(--muted-ink);
            font-size: 13px;
            font-weight: 800;
        }

        .user-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(120deg, var(--orange), var(--orange-soft));
            box-shadow: 0 0 0 4px rgba(255, 111, 31, .16);
        }

        .pill-btn,
        .pill-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 999px;
            border: 1px solid rgba(255, 122, 26, .22);
            background: rgba(27, 27, 27, .92);
            color: var(--muted-ink);
            font-size: 12px;
            font-weight: 800;
            padding: 10px 14px;
            text-decoration: none;
            cursor: pointer;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .pill-btn:hover,
        .pill-link:hover {
            transform: translateY(-1px);
            border-color: var(--orange-soft);
            box-shadow: 0 10px 20px rgba(255, 111, 31, .10);
        }

        .primary-link {
            border-color: rgba(255, 111, 31, .22);
            background: linear-gradient(120deg, var(--orange), var(--orange-soft));
            color: var(--accent-ink);
            box-shadow: 0 12px 24px rgba(255, 122, 26, .22);
        }

        .cart-link {
            position: relative;
            width: 48px;
            height: 48px;
            padding: 0;
            border-radius: 18px;
            flex-shrink: 0;
        }

        .cart-count {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(120deg, var(--orange), var(--orange-soft));
            color: var(--accent-ink);
            font-size: 11px;
            font-weight: 900;
        }

        .store-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .store-nav a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 15px;
            border-radius: 999px;
            border: 1px solid rgba(255, 122, 26, .22);
            background: rgba(26, 26, 26, .88);
            color: var(--muted-ink);
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            transition: transform .2s ease, border-color .2s ease, background .2s ease;
        }

        .store-nav a.active {
            border-color: rgba(255, 111, 31, .26);
            background: linear-gradient(120deg, var(--orange), var(--orange-soft));
            color: var(--accent-ink);
        }

        .store-nav a:hover {
            transform: translateY(-1px);
            border-color: var(--orange-soft);
        }

        .nav-index {
            font-size: 11px;
            color: inherit;
            opacity: .72;
        }

        main.page {
            position: relative;
            padding: 24px;
        }

        .page-stack {
            display: grid;
            gap: 18px;
        }

        .surface {
            border: 1px solid rgba(234, 182, 138, .78);
            border-radius: var(--radius-xl);
            background: linear-gradient(180deg, rgba(24,24,24,.96), rgba(18,18,18,.98));
            box-shadow: var(--shadow-soft);
        }

        .panel {
            border: 1px solid rgba(234, 182, 138, .7);
            border-radius: var(--radius-lg);
            padding: 18px;
            background: linear-gradient(180deg, var(--paper) 0%, var(--paper-soft) 100%);
            box-shadow: 0 16px 32px rgba(0, 0, 0, .22);
        }

        .title {
            margin: 0;
            color: var(--panel-ink);
            font-size: clamp(30px, 4vw, 48px);
            line-height: .96;
        }

        .section-title {
            margin: 0;
            color: var(--panel-ink);
            font-size: clamp(22px, 2.4vw, 30px);
        }

        .eyebrow {
            margin: 0 0 8px;
            color: var(--orange-soft);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .muted-main {
            margin: 0;
            color: var(--muted-ink);
            font-size: 14px;
            line-height: 1.6;
        }

        .grid-auto {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }

        .btn-main,
        .btn-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 16px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .btn-main {
            border: 1px solid rgba(255, 111, 31, .24);
            background: linear-gradient(120deg, var(--orange), var(--orange-soft));
            color: var(--accent-ink);
            box-shadow: 0 14px 26px rgba(255, 111, 31, .18);
        }

        .btn-soft {
            border: 1px solid rgba(234, 182, 138, .85);
            background: rgba(255, 249, 244, .94);
            color: #8a5837;
        }

        .btn-main:hover,
        .btn-soft:hover {
            transform: translateY(-1px);
        }

        .input-main,
        .select-main,
        .textarea-main {
            width: 100%;
            border: 1px solid rgba(234, 182, 138, .9);
            border-radius: 16px;
            padding: 13px 14px;
            background: rgba(255, 253, 249, .95);
            color: #28170e;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .input-main:focus,
        .select-main:focus,
        .textarea-main:focus {
            outline: none;
            border-color: var(--orange-soft);
            box-shadow: 0 0 0 5px rgba(255, 111, 31, .12);
            transform: translateY(-1px);
        }

        .label-main {
            display: block;
            margin-bottom: 7px;
            color: var(--panel-ink);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .product-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(234, 182, 138, .78);
            border-radius: 26px;
            padding: 16px;
            background: linear-gradient(180deg, #1b1b1b 0%, #131313 100%);
            box-shadow: 0 18px 34px rgba(0, 0, 0, .24);
        }

        .product-card > * {
            position: relative;
            z-index: 1;
        }

        .product-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 122, 26, .22), transparent 28%),
                linear-gradient(140deg, rgba(255, 255, 255, .04), transparent 50%);
            pointer-events: none;
        }

        .product-image-wrap {
            position: relative;
            aspect-ratio: 4 / 3;
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid rgba(234, 182, 138, .75);
            background: linear-gradient(145deg, #ffe9d7, #fffaf5);
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .36s ease, filter .36s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.07);
            filter: saturate(1.05);
        }

        @media (max-width: 980px) {
            .topbar-inner {
                grid-template-columns: 1fr;
            }

            .topbar-actions {
                align-items: stretch;
            }

            .session-strip,
            .store-nav {
                justify-content: flex-start;
            }
        }

        @media (max-width: 720px) {
            .store-shell { padding: 10px; }
            .topbar { padding: 14px; }
            main.page { padding: 16px; }
            .panel { padding: 15px; }
            .brand-cluster { align-items: flex-start; }
            .brand-mark { width: 52px; height: 52px; }
        }
    </style>
</head>
<body>
<div class="store-shell">
    <div class="container">
        <div class="store-frame">
            <header class="topbar">
                <div class="topbar-inner">
                    <div class="brand-cluster">
                        <div class="brand-mark">
                            <img src="/images/ico-pollo.jpg" alt="El Dorado">
                        </div>
                        <div class="brand-copy">
                            <p class="brand-kicker">Pollo a la Brasa y Parrillas</p>
                            <h1 class="brand-title">Pollos y Parrillas "El Dorado"</h1>
                            <p class="brand-subtitle">Un recorrido simple desde el antojo hasta el pago, con una experiencia de compra más clara, más elegante y mejor organizada.</p>
                        </div>
                    </div>

                    <div class="topbar-actions">
                        <div class="session-strip">
                            <span id="sessionUserName" class="user-pill">
                                <span class="user-dot"></span>
                                Invitado
                            </span>
                            <a id="clientLoginBtn" class="pill-link" href="/login">Login</a>
                            <button id="clientLogoutBtn" class="pill-btn" type="button">Salir</button>
                            <a class="pill-link cart-link primary-link" href="{{ route('store.cart') }}" aria-label="Ir al carrito">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M7 5h14l-1.5 8.5H9L7 5Z" stroke="#2d1406" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M7 5 6.2 3H3" stroke="#2d1406" stroke-width="1.8" stroke-linecap="round"/>
                                    <circle cx="10" cy="19" r="1.6" stroke="#2d1406" stroke-width="1.6"/>
                                    <circle cx="18" cy="19" r="1.6" stroke="#2d1406" stroke-width="1.6"/>
                                </svg>
                                <span id="cartCountBadge" class="cart-count">0</span>
                            </a>
                        </div>

                        <nav class="store-nav">
                            <a href="{{ route('store.products') }}" class="{{ request()->routeIs('store.products') ? 'active' : '' }}">
                                <span class="nav-index"></span> Productos
                            </a>
                            <a href="{{ route('store.about') }}" class="{{ request()->routeIs('store.about') ? 'active' : '' }}">
                                <span class="nav-index"></span> Quienes somos
                            </a>
                            <a href="{{ route('store.location') }}" class="{{ request()->routeIs('store.location') ? 'active' : '' }}">
                                <span class="nav-index"></span> Ubicacion
                            </a>
                            <a href="{{ route('store.experts') }}" class="{{ request()->routeIs('store.experts') ? 'active' : '' }}">
                                <span class="nav-index"></span> Expertos
                            </a>
                            <a href="{{ route('store.orders') }}" class="{{ request()->routeIs('store.orders') ? 'active' : '' }}">
                                <span class="nav-index"></span> Mis pedidos
                            </a>
                        </nav>
                    </div>
                </div>
            </header>

            <main class="page">
                <div class="page-stack">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</div>

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

const userNameEl = document.getElementById('sessionUserName');
const cartCountEl = document.getElementById('cartCountBadge');
const clientLoginBtn = document.getElementById('clientLoginBtn');
const clientLogoutBtn = document.getElementById('clientLogoutBtn');
const CLIENT_TIMEOUT_MS = 60 * 60 * 1000;

function parseUser() {
    const raw = localStorage.getItem('ed_user');
    if (!raw) return null;
    try { return JSON.parse(raw); } catch { return null; }
}

function parseSession() {
    const raw = localStorage.getItem('ed_session');
    if (!raw) return null;
    try { return JSON.parse(raw); } catch { return null; }
}

function saveSession(session) {
    localStorage.setItem('ed_session', JSON.stringify(session));
}

function clearClientSession() {
    localStorage.removeItem('ed_token');
    localStorage.removeItem('ed_user');
    localStorage.removeItem('ed_session');
    localStorage.removeItem('ed_cart');
    localStorage.removeItem('ed_last_tracking');
    localStorage.removeItem('ed_recent_trackings');
}

async function validateSessionWithServer() {
    const token = localStorage.getItem('ed_token');
    if (!token) return false;
    try {
        const res = await fetch('/api/v1/auth/me', {
            headers: { 'Authorization': `Bearer ${token}` },
        });
        const data = await res.json();
        if (!res.ok || !data.user || !data.user.is_active) return false;
        localStorage.setItem('ed_user', JSON.stringify(data.user));
        return true;
    } catch {
        return false;
    }
}

function touchSessionActivity() {
    const session = parseSession();
    if (!session) return;
    session.lastActivity = Date.now();
    session.expiresAt = Date.now() + CLIENT_TIMEOUT_MS;
    saveSession(session);
}

function updateTopBar() {
    const user = parseUser();
    userNameEl.innerHTML = `<span class="user-dot"></span>${user ? user.name : 'Invitado'}`;
    clientLogoutBtn.style.display = user ? 'inline-flex' : 'none';
    clientLoginBtn.style.display = user ? 'none' : 'inline-flex';

    const cart = JSON.parse(localStorage.getItem('ed_cart') || '[]');
    const count = cart.reduce((acc, item) => acc + Number(item.qty || 0), 0);
    cartCountEl.textContent = count;
}

async function initClientSession() {
    const token = localStorage.getItem('ed_token');
    const user = parseUser();
    if (!token || !user) {
        clearClientSession();
        updateTopBar();
        return;
    }

    let session = parseSession();
    if (!session || session.role !== (user.role || 'customer')) {
        session = { role: user.role || 'customer', lastActivity: Date.now(), expiresAt: Date.now() + CLIENT_TIMEOUT_MS };
        saveSession(session);
    }

    if (Date.now() > Number(session.expiresAt || 0)) {
        clearClientSession();
        updateTopBar();
        if (!window.location.pathname.startsWith('/login')) window.location.href = '/login';
        return;
    }

    const valid = await validateSessionWithServer();
    if (!valid) {
        clearClientSession();
        updateTopBar();
        if (!window.location.pathname.startsWith('/login')) window.location.href = '/login';
        return;
    }

    touchSessionActivity();
    updateTopBar();
}

window.addEventListener('storage', updateTopBar);
clientLogoutBtn.addEventListener('click', () => {
    clearClientSession();
    updateTopBar();
    window.location.href = '/login';
});
['click', 'keydown', 'mousemove', 'touchstart', 'scroll'].forEach(evt => {
    window.addEventListener(evt, touchSessionActivity, { passive: true });
});

setInterval(() => {
    const session = parseSession();
    if (!session) return;
    if (Date.now() > Number(session.expiresAt || 0)) {
        clearClientSession();
        updateTopBar();
        window.location.href = '/login';
    }
}, 15000);

initClientSession();
</script>

<div id="promoOverlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.72); padding:18px;">
    <div style="max-width:520px; margin:8vh auto 0; background:rgba(18,18,18,.98); border:1px solid rgba(255, 122, 26, .24); border-radius:24px; box-shadow: 0 26px 60px rgba(0, 0, 0, .32); overflow:hidden;">
        <div style="padding:14px 16px; border-bottom:1px solid rgba(255, 122, 26, .18); display:flex; align-items:center; justify-content:space-between; gap:12px;">
            <strong id="promoTitle" style="font-size:16px; line-height:1.2;">PromociÃ³n</strong>
            <button id="promoCloseBtn" type="button" class="pill-btn" style="padding:8px 12px;">Cerrar</button>
        </div>
        <div style="padding:16px;">
            <img id="promoImage" alt="" style="display:none; width:100%; height:220px; object-fit:cover; border-radius:18px; border:1px solid rgba(255, 122, 26, .22); margin-bottom:12px;">
            <div id="promoMessage" style="color:var(--ink); line-height:1.55; font-weight:800;"></div>
            <div id="promoBody" style="margin-top:10px; color:var(--ink-soft); line-height:1.6;"></div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:14px; flex-wrap:wrap;">
                <button id="promoRejectBtn" type="button" class="pill-btn">Rechazar</button>
                <button id="promoAcceptBtn" type="button" class="pill-btn primary-link">Ver</button>
            </div>
        </div>
    </div>
</div>

<div id="promoToast" style="display:none; position:fixed; right:18px; bottom:18px; z-index:9998; width:min(380px, calc(100vw - 36px));">
    <div style="background:rgba(18,18,18,.98); border:1px solid rgba(255, 122, 26, .24); border-radius:22px; box-shadow: 0 26px 60px rgba(0, 0, 0, .32); overflow:hidden;">
        <div style="padding:12px 14px; border-bottom:1px solid rgba(255, 122, 26, .18); display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <strong id="promoToastTitle" style="font-size:13px; line-height:1.2;">Nueva promociÃ³n</strong>
            <button id="promoToastCloseBtn" type="button" class="pill-btn" style="padding:8px 10px;">X</button>
        </div>
        <div style="padding:12px 14px;">
            <div id="promoToastMessage" style="color:var(--ink); line-height:1.45; font-weight:800;"></div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px; flex-wrap:wrap;">
                <button id="promoToastRejectBtn" type="button" class="pill-btn">Rechazar</button>
                <button id="promoToastAcceptBtn" type="button" class="pill-btn primary-link">Ver</button>
            </div>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
(() => {
    const key = @json(config('broadcasting.connections.pusher.key'));
    const cluster = @json(config('broadcasting.connections.pusher.options.cluster'));
    const channelName = 'mi-canal';
    const eventName = 'mi-evento';

    if (!key || !cluster || typeof Pusher === 'undefined') return;

    const overlay = document.getElementById('promoOverlay');
    const titleEl = document.getElementById('promoTitle');
    const messageEl = document.getElementById('promoMessage');
    const bodyEl = document.getElementById('promoBody');
    const imageEl = document.getElementById('promoImage');
    const closeBtn = document.getElementById('promoCloseBtn');
    const rejectBtn = document.getElementById('promoRejectBtn');
    const acceptBtn = document.getElementById('promoAcceptBtn');

    const toast = document.getElementById('promoToast');
    const toastTitleEl = document.getElementById('promoToastTitle');
    const toastMessageEl = document.getElementById('promoToastMessage');
    const toastCloseBtn = document.getElementById('promoToastCloseBtn');
    const toastRejectBtn = document.getElementById('promoToastRejectBtn');
    const toastAcceptBtn = document.getElementById('promoToastAcceptBtn');

    let lastPayload = null;

    function hideOverlay() { overlay.style.display = 'none'; }
    function showOverlay() { overlay.style.display = 'block'; }
    function hideToast() { toast.style.display = 'none'; }
    function showToast() { toast.style.display = 'block'; }
    function resolveImage(url) {
        const v = (url || '').toString().trim();
        if (!v) return '';
        if (v.startsWith('http://') || v.startsWith('https://')) return v;
        if (v.startsWith('/')) return `${window.location.origin}${v}`;
        return `${window.location.origin}/${v}`;
    }

    closeBtn.addEventListener('click', hideOverlay);
    rejectBtn.addEventListener('click', hideOverlay);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) hideOverlay(); });
    toastCloseBtn.addEventListener('click', hideToast);
    toastRejectBtn.addEventListener('click', hideToast);

    const pusher = new Pusher(key, { cluster, forceTLS: true });
    const channel = pusher.subscribe(channelName);

    channel.bind(eventName, (data) => {
        const payload = data && data.data ? data.data : data;
        const target = (payload?.target || '').toString().trim().toLowerCase();
        if (target && target !== 'web' && target !== 'all') return;

        lastPayload = payload || {};

        const title = (payload?.title || 'PromociÃ³n').toString();
        const message = (payload?.message || '').toString();
        const body = (payload?.body || '').toString();

        toastTitleEl.textContent = title;
        toastMessageEl.textContent = message || body || 'Tienes una nueva promociÃ³n.';

        toastAcceptBtn.textContent = (payload?.cta_label || 'Ver').toString();
        toastAcceptBtn.onclick = () => {
            const p = lastPayload || {};
            titleEl.textContent = title;
            messageEl.textContent = message;
            bodyEl.textContent = body;

            const img = resolveImage(p?.image_url || p?.imageUrl || '');
            if (img) {
                imageEl.src = img;
                imageEl.style.display = 'block';
            } else {
                imageEl.removeAttribute('src');
                imageEl.style.display = 'none';
            }

            acceptBtn.textContent = (p?.cta_label || 'Ver').toString();
            acceptBtn.onclick = () => showOverlay();

            hideToast();
            showOverlay();
        };

        showToast();
    });
})();
</script>
@yield('scripts')
</body>
</html>
