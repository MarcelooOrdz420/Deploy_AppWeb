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
            --orange: #FFD700;
            --orange-soft: #FFE135;
            --orange-deep: #FFC700;
            --cream: #FFD700;
            --cream-strong: #FFD700;
            --paper: #FFFFFF;
            --paper-soft: #FFFFFF;
            --ink: #FFFFFF;
            --ink-soft: #000000;
            --accent-ink: #000000;
            --panel-ink: #000000;
            --muted-ink: #000000;
            --line: rgba(255, 215, 0, .18);
            --line-strong: rgba(255, 215, 0, .28);
            --shadow-soft: 0 18px 40px rgba(255, 215, 0, .09);
            --shadow-strong: 0 26px 60px rgba(255, 215, 0, .13);
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
                radial-gradient(circle at top left, rgba(255, 215, 0, .14), transparent 22%),
                radial-gradient(circle at top right, rgba(255, 215, 0, .14), transparent 24%),
                linear-gradient(180deg, #FFD700 0%, #FFD700 54%, #FFD700 100%);
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
                linear-gradient(180deg, rgba(255,255,255,.98) 0%, rgba(255,248,239,.98) 100%);
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
            background: rgba(255, 251, 246, .92);
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
                linear-gradient(145deg, rgba(255,255,255,.98), rgba(255,245,232,.98));
            box-shadow: 0 12px 24px rgba(108, 56, 18, .12);
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
            color: #FFFFFF;
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
            background: rgba(255, 249, 242, .96);
            color: var(--ink);
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
            background: rgba(255, 249, 242, .98);
            color: var(--ink);
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
            background: rgba(255, 249, 242, .98);
            color: var(--ink);
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
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(200, 106, 45, .12);
            color: var(--orange-deep);
            font-size: 12px;
            line-height: 1;
            opacity: 1;
            flex-shrink: 0;
        }

        .action-symbol {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(200, 106, 45, .12);
            color: var(--orange-deep);
            font-size: 12px;
            line-height: 1;
            flex-shrink: 0;
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
            background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,246,236,.98));
            box-shadow: var(--shadow-soft);
        }

        .panel {
            border: 1px solid rgba(234, 182, 138, .7);
            border-radius: var(--radius-lg);
            padding: 18px;
            background: linear-gradient(180deg, var(--paper) 0%, var(--paper-soft) 100%);
            box-shadow: 0 16px 32px rgba(108, 56, 18, .10);
        }

        .title {
            margin: 0;
            color: var(--ink);
            font-size: clamp(30px, 4vw, 48px);
            line-height: .96;
        }

        .section-title {
            margin: 0;
            color: var(--ink);
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
            color: var(--ink);
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
            background: linear-gradient(180deg, #ffffff 0%, #fff6eb 100%);
            box-shadow: 0 18px 34px rgba(108, 56, 18, .10);
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


            border-radius: 0;
            border-left: 1px solid rgba(251, 180, 54, .34);
            border-right: 1px solid rgba(251, 180, 54, .34);
            border-top: 0;
            background: #fffaf4;
            box-shadow: none;
        }

        .store-frame::before {
            display: none;
        }

        .topbar {
            position: sticky;
            top: 0;
            padding: 0;
            background:
                linear-gradient(90deg, #17110d 0%, #1d1510 54%, #f5b400 54%, #ffc20e 100%);
            border-bottom: 1px solid rgba(255, 193, 15, .42);
        }

        .topbar-inner {
            grid-template-columns: .88fr 1.12fr;
            gap: 18px;
            padding: 16px 20px;
        }

        .brand-kicker,
        .brand-title,
        .brand-subtitle {
            color: #fff8ed;
        }

        .brand-kicker {
            color: #ffc45d;
        }

        .brand-subtitle {
            max-width: 520px;
            color: rgba(255, 248, 237, .78);
        }

        .brand-mark {
            width: 54px;
            height: 54px;
            border-radius: 10px;
            border-color: rgba(255,255,255,.24);
            background: rgba(255,255,255,.12);
            box-shadow: none;
        }

        .topbar-actions {
            gap: 10px;
        }

        .pill-btn,
        .pill-link,
        .store-nav a,
        .user-pill {
            min-height: 40px;
            border-radius: 999px;
            border-color: rgba(255,255,255,.42);
            background: rgba(255,255,255,.12);
            color: #fff8ed;
            box-shadow: 0 10px 18px rgba(32, 12, 0, .10);
        }

        .store-nav a.active,
        .primary-link {
            background: linear-gradient(135deg, #ff9f22, #d87525);
            color: #21140d;
            border-color: rgba(255,255,255,.52);
        }

        .action-symbol,
        .nav-index,
        .user-dot {
            background: rgba(255,255,255,.18);
            color: #fff8ed;
        }

        .cart-count {
            background: #ff9f22;
            color: #21140d;
        }

        main.page {
            padding: 0 24px 28px;
        }

        .page-stack {
            gap: 18px;
        }

        .surface,
        .panel,
        .product-card,
        .float-cart-panel {
            border-radius: 8px;
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
            .topbar { border-radius: 12px 12px 0 0; }
            .topbar-inner { padding: 14px; }
            main.page { padding: 0 12px 18px; }
            .panel { padding: 15px; }
            .brand-cluster { align-items: flex-start; }
            .brand-mark { width: 52px; height: 52px; }
        }

        /* Identidad final: tema naranja puro. */
        body,
        body[data-theme="dark"] {
            --orange: #FF8A18;
            --orange-soft: #FF6F1F;
            --orange-deep: #E87912;
            --cream: #FF8A18;
            --paper: #FFFFFF;
            --ink: #FFFFFF;
            --ink-soft: #000000;
            --line: rgba(255, 138, 24, .34);
            background:
                radial-gradient(circle at 12% 0%, rgba(255, 138, 24, .16), transparent 28%),
                linear-gradient(180deg, #FF8A18 0%, #FF8A18 45%, #FF8A18 100%);
            color: #FFFFFF;
        }

        .store-shell,
        body[data-theme="dark"] .store-shell {
            padding: 0;
            background: transparent;
        }

        .store-frame {
            max-width: 1280px;
            margin: 0 auto;
            border-radius: 0;
            background: #FFD700;
            border: 1px solid rgba(255, 215, 0, .18);
            box-shadow: 0 28px 80px rgba(0, 0, 0, .35);
            overflow: hidden;
        }

        .topbar {
            position: sticky;
            top: 0;
            border-radius: 0;
            border: 0;
            background:
                linear-gradient(90deg, #FFC700 0%, #FFC700 58%, #FFD700 58%, #FFD700 100%) !important;
            box-shadow: none;
        }

        .topbar-inner {
            grid-template-columns: minmax(320px, .95fr) minmax(360px, 1.05fr);
            padding: 18px 22px;
        }

        .brand-title {
            color: #FFFFFF;
            line-height: .95;
        }

        .brand-sub {
            color: #FFFFFF;
            max-width: 520px;
        }

        .brand-kicker {
            color: #FFFFFF !important;
        }

        .brand-mark {
            width: 56px;
            height: 56px;
            border-radius: 8px;
            border-color: rgba(255, 255, 255, .46);
            background: #FFFFFF;
        }

        .session-strip,
        .store-nav {
            justify-content: flex-end;
        }

        .pill-btn,
        .pill-link,
        .store-nav a,
        .user-pill,
        body[data-theme="dark"] .pill-btn,
        body[data-theme="dark"] .pill-link,
        body[data-theme="dark"] .store-nav a,
        body[data-theme="dark"] .user-pill {
            min-height: 38px;
            border-radius: 999px;
            background: rgba(232, 121, 18, .9);
            color: #FFFFFF;
            border-color: rgba(255, 255, 255, .32);
            box-shadow: none;
        }

        .store-nav a.active,
        .primary-link,
        body[data-theme="dark"] .store-nav a.active,
        body[data-theme="dark"] .primary-link {
            background: linear-gradient(135deg, #FF6F1F, #E87912);
            color: #FFFFFF;
            border-color: rgba(255, 255, 255, .48);
        }

        .nav-index,
        .action-symbol,
        body[data-theme="dark"] .nav-index,
        body[data-theme="dark"] .action-symbol {
            background: rgba(255, 255, 255, .22);
            color: #FFFFFF;
        }

        main.page {
            padding: 0 20px 28px;
            background: #FF8A18;
        }

        .surface,
        .panel,
        .product-card,
        body[data-theme="dark"] .surface,
        body[data-theme="dark"] .panel,
        body[data-theme="dark"] .product-card {
            background: #FFFFFF;
            border-color: rgba(255, 138, 24, .28);
            color: #000000;
        }

        @media (max-width: 860px) {
            .topbar,
            body[data-theme="dark"] .topbar {
                background: linear-gradient(180deg, #17110d 0%, #17110d 58%, #ffc20e 58%, #ffc20e 100%) !important;
            }

            .topbar-inner {
                grid-template-columns: 1fr;
            }

            .session-strip,
            .store-nav {
                justify-content: flex-start;
            }
        }
    </style>
    <link rel="stylesheet" href="/css/brand-refresh.css?v=20260618-pollia2">
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

                            <button id="clientAlertsBtn" class="pill-btn" type="button">
                                <span class="action-symbol">&#9679;</span>Avisos
                                <span id="clientAlertCount" class="cart-count" style="position:static; min-width:22px; height:22px;">0</span>
                            </button>
                            <a id="clientLoginBtn" class="pill-link" href="/login"><span class="action-symbol">&#8594;</span>Login</a>
                            <button id="clientLogoutBtn" class="pill-btn" type="button"><span class="action-symbol">&#8617;</span>Salir</button>
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
                                <span class="nav-index">&#9638;</span> Productos
                            </a>
                            <a href="{{ route('store.about') }}" class="{{ request()->routeIs('store.about') ? 'active' : '' }}">
                                <span class="nav-index">&#8962;</span> Nosotros
                            </a>
                            <a href="{{ route('store.location') }}" class="{{ request()->routeIs('store.location') ? 'active' : '' }}">
                                <span class="nav-index">&#8982;</span> Ubicacion
                            </a>
                            <a href="{{ route('store.experts') }}" class="{{ request()->routeIs('store.experts') ? 'active' : '' }}">
                                <span class="nav-index">&#10022;</span> Expertos
                            </a>
                            <a href="{{ route('store.orders') }}" class="{{ request()->routeIs('store.orders') ? 'active' : '' }}">
                                <span class="nav-index">&#8811;</span> Mis pedidos
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
const clientAlertsBtn = document.getElementById('clientAlertsBtn');
const clientAlertCount = document.getElementById('clientAlertCount');
const CLIENT_TIMEOUT_MS = 60 * 60 * 1000;
const CLIENT_ALERTS_KEY = 'ed_order_alert_count';

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

function getClientAlertCount() {
    return Number(localStorage.getItem(CLIENT_ALERTS_KEY) || '0');
}

function setClientAlertCount(value) {
    const next = Math.max(0, Number(value || 0));
    localStorage.setItem(CLIENT_ALERTS_KEY, String(next));
    if (clientAlertCount) clientAlertCount.textContent = String(next);
}



let cartSyncTimer = null;

window.edSyncCartDraft = function (forceCart = null) {
    const token = localStorage.getItem('ed_token');
    const user = parseUser();
    if (!token || !user) return;

    const cart = Array.isArray(forceCart)
        ? forceCart
        : JSON.parse(localStorage.getItem('ed_cart') || '[]');

    clearTimeout(cartSyncTimer);
    cartSyncTimer = setTimeout(async () => {
        try {
            await fetch('/api/v1/cart-recovery', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify({
                    source: 'web',
                    items: cart.map((item) => ({
                        id: Number(item.id || 0),
                        name: String(item.name || ''),
                        category: String(item.category || ''),
                        price: Number(item.price || 0),
                        qty: Number(item.qty || 0),
                        image_url: String(item.image_url || ''),
                    })),
                }),
            });
        } catch {}
    }, 350);
}

function clearClientSession() {
    localStorage.removeItem('ed_token');
    localStorage.removeItem('ed_user');
    localStorage.removeItem('ed_session');
    localStorage.removeItem('ed_cart');
    localStorage.removeItem('ed_last_tracking');
    localStorage.removeItem('ed_recent_trackings');
    localStorage.removeItem(CLIENT_ALERTS_KEY);
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
    setClientAlertCount(getClientAlertCount());
    applyStoreTheme(getStoreTheme());
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
    window.edSyncCartDraft();
}

window.addEventListener('storage', () => {
    updateTopBar();
    window.edSyncCartDraft();
});
clientAlertsBtn?.addEventListener('click', () => {
    setClientAlertCount(0);
    if (!window.location.pathname.startsWith('/mis-pedidos')) {
        window.location.href = '/mis-pedidos';
    }
});

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

<style>
    @media (max-width: 720px) {
        #promoOverlay {
            overflow-y: auto;
        }

        #promoOverlay > div {
            margin-top: 3vh !important;
            border-radius: 22px !important;
        }

        #promoOverlay > div > div:nth-child(2) {
            grid-template-columns: 1fr !important;
        }

        #promoMessage {
            font-size: 30px !important;
        }

        #promoImage {
            height: 220px !important;
            border-radius: 18px !important;
        }
    }
</style>

<div id="promoOverlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(14,10,7,.82); padding:18px;">
    <div style="max-width:720px; margin:6vh auto 0; background:linear-gradient(90deg,#1b130f 0%,#2a1d16 55%,#ff7c18 55%,#ff8f1f 100%); border:1px solid rgba(255, 188, 114, .24); border-radius:30px; box-shadow:0 30px 70px rgba(0, 0, 0, .38); overflow:hidden;">
        <div style="padding:14px 18px; display:flex; align-items:center; justify-content:space-between; gap:12px; color:#fff7ed; border-bottom:1px solid rgba(255,255,255,.08);">
            <strong id="promoTitle" style="font-size:16px; line-height:1.2;">PromociÃ³n</strong>
            <button id="promoCloseBtn" type="button" class="pill-btn" style="padding:8px 12px; background:rgba(255,255,255,.12); color:#fff7ed; border-color:rgba(255,255,255,.18);">Cerrar</button>
        </div>
        <div style="display:grid; grid-template-columns:1.05fr .95fr; gap:0;">
            <div style="padding:26px 24px; color:#fff7ed; background:radial-gradient(circle at top left, rgba(255,255,255,.08), transparent 22%), linear-gradient(135deg,#201712 0%,#160f0c 48%,#2b1a14 100%);">
                <div style="font-size:11px; letter-spacing:.2em; text-transform:uppercase; color:rgba(255,255,255,.56); margin-bottom:10px;">Promo del dia</div>
                <div id="promoMessage" style="font-size:38px; line-height:.92; font-weight:900; text-transform:uppercase; text-shadow:0 8px 18px rgba(0,0,0,.28);">Nueva promo</div>
                <div id="promoBody" style="margin-top:12px; color:rgba(255,247,237,.82); line-height:1.6; max-width:280px;"></div>
                <div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
                    <button id="promoAcceptBtn" type="button" class="pill-btn primary-link" style="padding:12px 18px;">Ver</button>
                    <button id="promoRejectBtn" type="button" class="pill-btn" style="padding:12px 18px; background:rgba(255,255,255,.12); color:#fff7ed; border-color:rgba(255,255,255,.18);">Cerrar</button>
                </div>
            </div>
            <div style="padding:18px; display:flex; align-items:center; justify-content:center; background:radial-gradient(circle at center, rgba(255,255,255,.18), transparent 44%), linear-gradient(135deg,#ff7c18 0%,#ff931f 100%);">
                <img id="promoImage" alt="" style="display:none; width:100%; height:320px; object-fit:cover; border-radius:28px; border:4px solid rgba(255,255,255,.34); box-shadow:0 22px 38px rgba(84,32,0,.22);">
            </div>
        </div>
    </div>
</div>

<div id="promoToast" style="display:none; position:fixed; right:18px; bottom:18px; z-index:9998; width:min(380px, calc(100vw - 36px));">
    <div style="background:linear-gradient(90deg,#1a120e 0%,#2b1b14 58%,#ff7e1c 58%,#ff8f21 100%); border:1px solid rgba(255, 188, 114, .24); border-radius:24px; box-shadow: 0 26px 60px rgba(0, 0, 0, .32); overflow:hidden;">
        <div style="padding:12px 14px; border-bottom:1px solid rgba(255,255,255,.08); display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <strong id="promoToastTitle" style="font-size:13px; line-height:1.2;">Nueva promociÃ³n</strong>
            <button id="promoToastCloseBtn" type="button" class="pill-btn" style="padding:8px 10px; background:rgba(255,255,255,.12); color:#fff7ed; border-color:rgba(255,255,255,.18);">X</button>
        </div>
        <div style="padding:14px;">
            <div id="promoToastMessage" style="color:#fff7ed; line-height:1.24; font-size:22px; font-weight:900; text-transform:uppercase;"></div>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px; flex-wrap:wrap;">
                <button id="promoToastRejectBtn" type="button" class="pill-btn" style="background:rgba(255,255,255,.12); color:#fff7ed; border-color:rgba(255,255,255,.18);">Cerrar</button>
                <button id="promoToastAcceptBtn" type="button" class="pill-btn primary-link">Ver</button>
            </div>
        </div>
    </div>
</div>

<div id="orderToast" style="display:none; position:fixed; left:18px; bottom:18px; z-index:9998; width:min(380px, calc(100vw - 36px));">
    <div style="background:rgba(18,18,18,.98); border:1px solid rgba(255, 122, 26, .24); border-radius:22px; box-shadow: 0 26px 60px rgba(0, 0, 0, .32); overflow:hidden;">
        <div style="padding:12px 14px; border-bottom:1px solid rgba(255, 122, 26, .18); display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <strong id="orderToastTitle" style="font-size:13px; line-height:1.2;">Pedido actualizado</strong>
            <button id="orderToastCloseBtn" type="button" class="pill-btn" style="padding:8px 10px;">X</button>
        </div>
        <div style="padding:12px 14px;">
            <div id="orderToastMessage" style="color:var(--ink); line-height:1.45; font-weight:800;"></div>
            <div id="orderToastBody" style="margin-top:8px; color:var(--ink-soft); line-height:1.55;"></div>
        </div>
    </div>
</div>

<style>
    .pollia-launcher {
        position: fixed;
        right: 18px;
        bottom: 18px;
        z-index: 9997;
        width: 58px;
        height: 58px;
        border: 0;
        border-radius: 18px;
        background: linear-gradient(135deg, #ffbf00, #ff7a18);
        color: #160b02;
        box-shadow: 0 18px 42px rgba(0, 0, 0, .34);
        cursor: pointer;
        font-weight: 950;
        letter-spacing: 0;
    }

    .pollia-widget {
        position: fixed;
        right: 18px;
        bottom: 88px;
        z-index: 9997;
        display: none;
        width: min(390px, calc(100vw - 32px));
        height: min(560px, calc(100vh - 118px));
        overflow: hidden;
        border: 1px solid rgba(255, 191, 0, .30);
        border-radius: 12px;
        background: #fffaf0;
        color: #1b1108;
        box-shadow: 0 28px 70px rgba(0, 0, 0, .38);
    }

    .pollia-widget.open {
        display: grid;
        grid-template-rows: auto 1fr auto;
    }

    .pollia-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        background: #050505;
        color: #fffaf0;
    }

    .pollia-title {
        display: grid;
        gap: 2px;
        min-width: 0;
    }

    .pollia-title strong {
        font-size: 14px;
        line-height: 1.1;
    }

    .pollia-title span {
        color: rgba(255, 250, 240, .70);
        font-size: 11px;
    }

    .pollia-close {
        width: 34px;
        height: 34px;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 10px;
        background: rgba(255,255,255,.08);
        color: #fffaf0;
        cursor: pointer;
        font-weight: 900;
    }

    .pollia-log {
        display: flex;
        flex-direction: column;
        gap: 10px;
        overflow-y: auto;
        padding: 14px;
        background: linear-gradient(180deg, #fffaf0 0%, #fff6e8 100%);
    }

    .pollia-msg {
        max-width: 86%;
        padding: 10px 12px;
        border-radius: 12px;
        font-size: 13px;
        line-height: 1.45;
    }

    .pollia-msg p {
        margin: 0 0 8px;
    }

    .pollia-msg p:last-child {
        margin-bottom: 0;
    }

    .pollia-msg .pollia-heading {
        display: block;
        margin: 6px 0 5px;
        font-weight: 950;
        color: #7a2f00;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .pollia-msg ul {
        margin: 6px 0 10px;
        padding-left: 18px;
    }

    .pollia-msg li {
        margin: 4px 0;
    }

    .pollia-msg.bot {
        align-self: flex-start;
        border: 1px solid rgba(255, 191, 0, .24);
        background: #ffffff;
        color: #1b1108;
    }

    .pollia-msg.user {
        align-self: flex-end;
        background: #ffbf00;
        color: #160b02;
        font-weight: 800;
    }

    .pollia-form {
        display: flex;
        gap: 8px;
        padding: 10px;
        border-top: 1px solid rgba(255, 191, 0, .22);
        background: #fffaf0;
    }

    .pollia-form input {
        min-width: 0;
        flex: 1;
        border: 1px solid rgba(255, 191, 0, .30) !important;
        border-radius: 10px;
        padding: 11px 12px;
        background: #ffffff !important;
        color: #1b1108 !important;
    }

    .pollia-form button {
        width: 46px;
        border: 0 !important;
        border-radius: 10px;
        background: #ffbf00 !important;
        color: #160b02 !important;
        cursor: pointer;
        font-weight: 950;
    }

    @media (max-width: 720px) {
        .pollia-launcher {
            right: 12px;
            bottom: 12px;
        }

        .pollia-widget {
            right: 10px;
            bottom: 78px;
            width: calc(100vw - 20px);
            height: min(560px, calc(100vh - 96px));
        }
    }
</style>

<button id="polliaLauncher" type="button" class="pollia-launcher" aria-label="Abrir POLL-IA">IA</button>
<section id="polliaWidget" class="pollia-widget" aria-label="Asistente POLL-IA">
    <div class="pollia-head">
        <div class="pollia-title">
            <strong>POLL-IA</strong>
            <span id="polliaStatus">Asistente de Pollos y Parrillas El Dorado</span>
        </div>
        <button id="polliaClose" type="button" class="pollia-close" aria-label="Cerrar">X</button>
    </div>
    <div id="polliaLog" class="pollia-log"></div>
    <form id="polliaForm" class="pollia-form">
        <input id="polliaInput" type="text" maxlength="1200" autocomplete="off" placeholder="Escribe tu consulta..." aria-label="Mensaje para POLL-IA">
        <button id="polliaSend" type="submit" aria-label="Enviar">></button>
    </form>
</section>

<script>
(() => {
    const launcher = document.getElementById('polliaLauncher');
    const widget = document.getElementById('polliaWidget');
    const closeBtn = document.getElementById('polliaClose');
    const form = document.getElementById('polliaForm');
    const input = document.getElementById('polliaInput');
    const log = document.getElementById('polliaLog');
    const statusEl = document.getElementById('polliaStatus');
    const sendBtn = document.getElementById('polliaSend');

    if (!launcher || !widget || !form || !input || !log) return;

    const guestKey = 'ed_pollia_guest_session';
    let booted = false;
    let sending = false;

    function guestSession() {
        const existing = localStorage.getItem(guestKey);
        if (existing) return existing;
        const id = `web-${Date.now()}-${Math.random().toString(16).slice(2)}`;
        localStorage.setItem(guestKey, id);
        return id;
    }

    function addMessage(role, text) {
        const item = document.createElement('div');
        item.className = `pollia-msg ${role}`;
        if (role === 'bot') {
            item.innerHTML = formatBotReply(text);
        } else {
            item.textContent = text;
        }
        log.appendChild(item);
        log.scrollTop = log.scrollHeight + 80;
        return item;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatBotReply(text) {
        const lines = String(text || '').replace(/\r\n/g, '\n').split('\n');
        const html = [];
        let list = [];

        function flushList() {
            if (!list.length) return;
            html.push(`<ul>${list.map((line) => `<li>${line}</li>`).join('')}</ul>`);
            list = [];
        }

        lines.forEach((raw) => {
            const line = raw.trim();
            if (!line) {
                flushList();
                return;
            }

            const bullet = line.match(/^[-•]\s*(.+)$/);
            if (bullet) {
                list.push(escapeHtml(bullet[1]));
                return;
            }

            flushList();
            const looksLikeHeading = line.length <= 56 && !/[.!?]$/.test(line);
            if (looksLikeHeading) {
                html.push(`<span class="pollia-heading">${escapeHtml(line)}</span>`);
            } else {
                html.push(`<p>${escapeHtml(line)}</p>`);
            }
        });

        flushList();
        return html.join('') || '<p>No tengo una respuesta clara todavia.</p>';
    }

    async function checkStatus() {
        try {
            const res = await fetch('/api/v1/chatbot/status', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (statusEl) {
                const provider = (data.provider || 'ia').toString().toUpperCase();
                const model = (data.model || '').toString();
                statusEl.textContent = data.ok ? `${provider}${model ? ` - ${model}` : ''}` : 'Modo respuesta local';
            }
        } catch {
            if (statusEl) statusEl.textContent = 'Modo respuesta local';
        }
    }

    function openWidget() {
        widget.classList.add('open');
        launcher.style.display = 'none';
        if (!booted) {
            booted = true;
            addMessage('bot', 'Hola, soy POLL-IA. Puedo ayudarte con productos, pedidos, pagos, horarios, delivery y ubicacion.');
            checkStatus();
        }
        setTimeout(() => input.focus(), 80);
    }

    function closeWidget() {
        widget.classList.remove('open');
        launcher.style.display = 'block';
    }

    async function sendMessage(raw) {
        const message = (raw || '').trim();
        if (!message || sending) return;

        sending = true;
        sendBtn.disabled = true;
        addMessage('user', message);
        input.value = '';
        const typing = addMessage('bot', 'Escribiendo...');

        try {
            const token = localStorage.getItem('ed_token') || '';
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            };
            const payload = { message };

            if (token) {
                headers.Authorization = `Bearer ${token}`;
            } else {
                payload.guest_session = guestSession();
            }

            const res = await fetch('/api/v1/chatbot/message', {
                method: 'POST',
                headers,
                body: JSON.stringify(payload),
            });
            const data = await res.json().catch(() => ({}));
            typing.remove();

            if (!res.ok) {
                addMessage('bot', data.message || 'No pude procesar tu consulta en este momento.');
                return;
            }

            addMessage('bot', (data.reply || '').toString().trim() || 'No tengo una respuesta clara todavia.');
        } catch {
            typing.remove();
            addMessage('bot', 'No pude conectar con el asistente. Revisa tu conexion e intentalo de nuevo.');
        } finally {
            sending = false;
            sendBtn.disabled = false;
            input.focus();
        }
    }

    launcher.addEventListener('click', openWidget);
    closeBtn?.addEventListener('click', closeWidget);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        sendMessage(input.value);
    });
})();
</script>

<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
(() => {
    const key = @json(config('broadcasting.connections.pusher.key'));
    const cluster = @json(config('broadcasting.connections.pusher.options.cluster'));
    const host = @json(config('broadcasting.connections.pusher.options.host'));
    const port = @json(config('broadcasting.connections.pusher.options.port'));
    const scheme = @json(config('broadcasting.connections.pusher.options.scheme'));
    const channelName = 'mi-canal';
    const eventName = 'mi-evento';

    if (!key || typeof Pusher === 'undefined') return;

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

    const pusherOptions = {
        forceTLS: scheme === 'https',
    };
    if (cluster) pusherOptions.cluster = cluster;
    if (host) pusherOptions.wsHost = host;
    if (port) {
        pusherOptions.wsPort = Number(port);
        pusherOptions.wssPort = Number(port);
    }

    const pusher = new Pusher(key, pusherOptions);
    const channel = pusher.subscribe(channelName);

    const handlePromoEvent = (data) => {
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
    };

    [eventName, `.${eventName}`, 'App\\Events\\OfferNotificationSent'].forEach((name) => {
        channel.bind(name, handlePromoEvent);
    });
})();
</script>
<script>
(() => {
    const key = @json(config('broadcasting.connections.pusher.key'));
    const cluster = @json(config('broadcasting.connections.pusher.options.cluster'));
    const host = @json(config('broadcasting.connections.pusher.options.host'));
    const port = @json(config('broadcasting.connections.pusher.options.port'));
    const scheme = @json(config('broadcasting.connections.pusher.options.scheme'));
    const orderToast = document.getElementById('orderToast');
    const orderToastTitle = document.getElementById('orderToastTitle');
    const orderToastMessage = document.getElementById('orderToastMessage');
    const orderToastBody = document.getElementById('orderToastBody');
    const orderToastCloseBtn = document.getElementById('orderToastCloseBtn');
    let booted = false;

    if (!key || typeof Pusher === 'undefined') return;

    function authEndpointFor(token) {
        const apiBase = @json(config('app.api_base_url'));
        const base = (apiBase || '').toString().replace(/\/+$/, '');
        return `${base}/api/v1/pusher/auth?token=${encodeURIComponent(token)}`;
    }

    function playClientSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.value = 880;
            gain.gain.value = 0.03;
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.18);
        } catch {}
    }

    function showOrderUpdate(payload) {
        if (!orderToast) return;
        orderToastTitle.textContent = (payload?.title || 'Pedido actualizado').toString();
        orderToastMessage.textContent = (payload?.message || 'Tu pedido tiene novedades.').toString();
        orderToastBody.textContent = (payload?.body || '').toString();
        orderToast.style.display = 'block';
        if (typeof getClientAlertCount === 'function' && typeof setClientAlertCount === 'function') {
            setClientAlertCount(getClientAlertCount() + 1);
        }
        playClientSound();
        window.dispatchEvent(new CustomEvent('ed:order-status-updated', { detail: payload || {} }));
    }

    orderToastCloseBtn?.addEventListener('click', () => {
        orderToast.style.display = 'none';
    });

    window.edBootRealtimeClient = function () {
        if (booted) return;
        booted = true;

        const token = localStorage.getItem('ed_token') || '';
        const user = typeof parseUser === 'function' ? parseUser() : null;
        if (!token || !user?.id) return;

        const pusherOptions = {
            forceTLS: scheme === 'https',
            authEndpoint: authEndpointFor(token),
        };
        if (cluster) pusherOptions.cluster = cluster;
        if (host) pusherOptions.wsHost = host;
        if (port) {
            pusherOptions.wsPort = Number(port);
            pusherOptions.wssPort = Number(port);
        }

        const pusher = new Pusher(key, pusherOptions);
        const privateChannel = pusher.subscribe(`private-user.${user.id}`);

        ['order.status.updated', '.order.status.updated', 'App\\Events\\OrderStatusUpdatedForUser'].forEach((name) => {
            privateChannel.bind(name, (data) => {
                const payload = data && data.data ? data.data : data;
                showOrderUpdate(payload || {});
            });
        });
    };

    window.edBootRealtimeClient();
})();
</script>
@yield('scripts')
</body>
</html>
