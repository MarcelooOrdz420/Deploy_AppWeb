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

        .store-footer {
            position:relative;
            z-index:2;
            padding:42px 30px 28px;
            background:linear-gradient(135deg,#3b0b08 0%,#65100c 58%,#7a170e 100%);
            color:#fff8ef;
        }
        .store-footer-grid { display:grid; grid-template-columns:1.15fr 1fr 1fr 1.15fr; gap:34px; }
        .footer-brand { display:grid; align-content:start; gap:16px; }
        .footer-logo { display:flex; align-items:center; gap:12px; text-decoration:none; }
        .footer-logo img { width:62px; height:62px; padding:5px; border-radius:14px; object-fit:contain; background:#fff; }
        .footer-logo strong { font-size:22px; line-height:1.05; }
        .footer-socials { display:flex; flex-wrap:wrap; gap:9px; }
        .footer-socials a { display:grid; place-items:center; min-width:42px; height:42px; padding:0 10px; border:1px solid rgba(255,255,255,.4); border-radius:50px; color:#fff; text-decoration:none; font-weight:900; transition:.2s ease; }
        .footer-socials a:hover { transform:translateY(-3px); background:#ff7a18; border-color:#ffb06e; }
        .footer-column h2 { margin:0 0 14px; color:#fff; font-size:16px; text-transform:uppercase; letter-spacing:.03em; }
        .footer-links { display:grid; gap:10px; }
        .footer-links a,.footer-contact span { color:rgba(255,248,239,.88); text-decoration:none; font-size:14px; line-height:1.45; }
        .footer-links a:hover { color:#ffc20e; transform:translateX(3px); }
        .footer-contact { display:grid; gap:8px; }
        .footer-copy { margin:20px 0 0; color:rgba(255,255,255,.68); font-size:12px; }
        .footer-whatsapp { position:absolute; right:24px; bottom:20px; display:grid; place-items:center; width:58px; height:58px; border-radius:50%; background:#079b71; color:#fff; text-decoration:none; font-size:26px; border:4px solid rgba(255,255,255,.9); box-shadow:0 12px 28px rgba(0,0,0,.25); }

        .scroll-reveal { opacity:0; transform:translateY(30px); transition:opacity .72s ease,transform .72s cubic-bezier(.2,.7,.2,1); }
        .scroll-reveal.reveal-left { transform:translateX(-34px); }
        .scroll-reveal.reveal-right { transform:translateX(34px); }
        .scroll-reveal.is-visible { opacity:1; transform:none; }

        @media (max-width:900px) {
            .store-footer-grid { grid-template-columns:1fr 1fr; }
        }
        @media (max-width:600px) {
            .store-footer { padding:32px 20px 88px; }
            .store-footer-grid { grid-template-columns:1fr; gap:28px; }
            .footer-whatsapp { right:20px; bottom:20px; }
        }
        @media (prefers-reduced-motion:reduce) {
            .scroll-reveal { opacity:1 !important; transform:none !important; transition:none !important; }
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
    <link rel="stylesheet" href="/css/brand-refresh.css?v=20260804-fullwidth">
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
            <footer class="store-footer">
                <div class="store-footer-grid">
                    <section class="footer-brand">
                        <a class="footer-logo" href="{{ route('store.products') }}">
                            <img src="/images/ico-pollo.jpg" alt="Pollos y Parrillas El Dorado">
                            <strong>El Dorado<br>Pollos y Parrillas</strong>
                        </a>
                        <div class="footer-socials" aria-label="Redes sociales">
                            <a href="#" aria-label="Facebook">f</a>
                            <a href="#" aria-label="Instagram">IG</a>
                            <a href="#" aria-label="TikTok">TT</a>
                            <a id="footerWhatsappSocial" href="#" aria-label="WhatsApp">WA</a>
                        </div>
                        <p class="footer-copy">&copy; {{ date('Y') }} Pollos y Parrillas El Dorado. Todos los derechos reservados.</p>
                    </section>
                    <section class="footer-column">
                        <h2>Atención al cliente</h2>
                        <nav class="footer-links">
                            <a href="{{ route('store.products') }}">Ayuda para comprar</a>
                            <a href="{{ route('store.orders') }}">Seguimiento de pedidos</a>
                            <a href="{{ route('store.cart') }}">Delivery y formas de pago</a>
                            <a href="#">Preguntas frecuentes</a>
                            <a href="#">Libro de reclamaciones</a>
                        </nav>
                    </section>
                    <section class="footer-column">
                        <h2>Conócenos</h2>
                        <nav class="footer-links">
                            <a href="{{ route('store.about') }}">Nosotros</a>
                            <a href="{{ route('store.location') }}">Ubicación</a>
                            <a href="{{ route('store.experts') }}">Expertos</a>
                            <a href="{{ route('store.products') }}">Nuestro menú</a>
                            <a href="#">Trabaja con nosotros</a>
                        </nav>
                    </section>
                    <section class="footer-column">
                        <h2>Contacto</h2>
                        <div class="footer-contact">
                            <span id="footerLocationName">Local principal El Dorado</span>
                            <span id="footerAddress">Jr. Cuzco, Huancayo, Perú</span>
                            <span id="footerHours">Atención continua hasta las 11:00 PM</span>
                            <span>Celular: <span id="footerPhone">964 900 990</span></span>
                        </div>
                    </section>
                </div>
                <a id="footerWhatsapp" class="footer-whatsapp" href="#" aria-label="Contactar por WhatsApp">WA</a>
            </footer>
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

function clearCustomerSessionData() {
    [
        'ed_token',
        'ed_user',
        'ed_session',
        'ed_cart',
        'ed_last_tracking',
        'ed_recent_trackings',
        'ed_order_statuses',
        'ed_pollia_checkout_prefill_v1',
        'ed_pollia_pending_cart_v1',
        'ed_pollia_guest_session',
        'ed_checkout_draft',
        'ed_checkout_data',
        'ed_customer_data',
        'ed_delivery_data',
        'ed_payment_method',
        'ed_payment_draft',
        'ed_order_draft',
        'ed_izipay_data',
        'ed_pending_order',
        CLIENT_ALERTS_KEY,
    ].forEach(key => localStorage.removeItem(key));
    ['ed_receipt_preview', 'ed_checkout_draft', 'ed_izipay_data', 'ed_pending_order', 'ed_guided_order_note']
        .forEach(key => sessionStorage.removeItem(key));
    clearTimeout(cartSyncTimer);
    document.body.classList.remove('cart-open', 'cart-panel-open', 'cart-has-items');
    window.dispatchEvent(new CustomEvent('ed:customer-session-cleared'));
    window.dispatchEvent(new Event('storage'));
}
window.clearCustomerSessionData = clearCustomerSessionData;

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
        clearCustomerSessionData();
        updateTopBar();
        return;
    }

    let session = parseSession();
    if (!session || session.role !== (user.role || 'customer')) {
        session = { role: user.role || 'customer', lastActivity: Date.now(), expiresAt: Date.now() + CLIENT_TIMEOUT_MS };
        saveSession(session);
    }

    if (Date.now() > Number(session.expiresAt || 0)) {
        clearCustomerSessionData();
        updateTopBar();
        if (!window.location.pathname.startsWith('/login')) window.location.href = '/login';
        return;
    }

    const valid = await validateSessionWithServer();
    if (!valid) {
        clearCustomerSessionData();
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

clientLogoutBtn.addEventListener('click', async () => {
    const token = localStorage.getItem('ed_token');
    if (token) {
        try {
            await fetch('/api/v1/auth/logout', {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
                cache: 'no-store',
            });
        } catch {}
    }
    clearCustomerSessionData();
    updateTopBar();
    window.location.replace('/login');
});
['click', 'keydown', 'mousemove', 'touchstart', 'scroll'].forEach(evt => {
    window.addEventListener(evt, touchSessionActivity, { passive: true });
});

setInterval(() => {
    const session = parseSession();
    if (!session) return;
    if (Date.now() > Number(session.expiresAt || 0)) {
        clearCustomerSessionData();
        updateTopBar();
        window.location.replace('/login');
    }
}, 15000);

initClientSession();
</script>

<style>
    .pollia-purchase-flow { overflow-y:auto; overflow-x:hidden; padding:12px; background:#FFF8F2; }
    .pollia-purchase-card { display:grid; gap:12px; padding:14px; border:1px solid #F0C9AA; border-radius:14px; background:#FFFDF9; color:#25170F; }
    .pollia-purchase-card h3 { margin:0; color:#25170F; font-size:17px; }
    .pollia-purchase-card label { display:grid; gap:6px; color:#68432E; font-size:12px; font-weight:800; }
    .pollia-purchase-card input,.pollia-purchase-card select,.pollia-purchase-card textarea { width:100%; min-height:44px; padding:10px 12px; border:1px solid #EAB68A; border-radius:12px; background:#fff; color:#25170F; }
    .pollia-purchase-card textarea { min-height:78px; resize:vertical; }
    .pollia-purchase-card [data-guided-location] { min-height:44px; padding:10px 14px; border:1px solid #C94700; border-radius:12px; background:#FFF1E8; color:#9D3500; font-weight:900; cursor:pointer; }
    .pollia-purchase-card [data-guided-location-status] { color:#68432E; font-size:12px; line-height:1.45; }
    .pollia-purchase-actions { display:flex; flex-wrap:wrap; gap:8px; position:sticky; bottom:0; padding-top:4px; background:#FFFDF9; }
    .pollia-purchase-actions button { min-height:44px; padding:10px 14px; border-radius:12px; border:1px solid #EAB68A; background:#fff; color:#25170F; font-weight:900; }
    .pollia-purchase-actions .primary { background:#FF6F1F; color:#fff; border-color:#C94700; }
    .pollia-product-summary { display:grid; gap:6px; padding:10px; border-radius:12px; background:#FFF1E3; color:#25170F; }
    #promoOverlay #promoTitle { color:#fff!important; background:transparent!important; opacity:1!important; }
    #promoOverlay #promoMessage { color:#fff8ef!important; }
    #promoOverlay #promoBody { color:#ffd8bd!important; }
    #promoToast > div { background:linear-gradient(135deg,#4f100c,#8b1f13)!important; }
    #promoToastTitle,#promoToastMessage { color:#fff!important; }
    #promoOverlay .promo-copy { background:linear-gradient(145deg,#49100c,#77180f)!important; }
    #promoOverlay .promo-media { background:linear-gradient(145deg,#ff9b30,#ff6d0b)!important; }

    @media (max-width: 720px) {
        #promoOverlay {
            overflow-y: auto;
            padding:10px !important;
        }

        #promoOverlay > div {
            margin: 2vh auto !important;
            border-radius: 22px !important;
            max-height:96vh;
            overflow-y:auto !important;
        }

        #promoOverlay > div > div:nth-child(2) {
            grid-template-columns: 1fr !important;
        }

        #promoMessage {
            font-size: clamp(24px, 8vw, 32px) !important;
            line-height:1.02 !important;
        }

        #promoImage {
            height: min(260px, 34vh) !important;
            border-radius: 18px !important;
        }

        #promoOverlay .promo-copy { padding:20px 18px !important; }
        #promoOverlay .promo-media { padding:12px !important; }
        #promoOverlay .promo-actions { display:grid !important; grid-template-columns:1fr 1fr; }
        #promoOverlay .promo-actions button { width:100%; margin:0; }
        #promoToast .promo-toast-actions { display:grid !important; grid-template-columns:1fr 1fr; }
        #promoToast .promo-toast-actions button { width:100%; margin:0; }
    }
</style>

<div id="promoOverlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(14,10,7,.82); padding:18px;">
    <div style="max-width:720px; margin:6vh auto 0; background:linear-gradient(90deg,#1b130f 0%,#2a1d16 55%,#ff7c18 55%,#ff8f1f 100%); border:1px solid rgba(255, 188, 114, .24); border-radius:30px; box-shadow:0 30px 70px rgba(0, 0, 0, .38); overflow:hidden;">
        <div style="padding:14px 18px; display:flex; align-items:center; justify-content:space-between; gap:12px; color:#fff7ed; border-bottom:1px solid rgba(255,255,255,.08);">
            <strong id="promoTitle" style="font-size:16px; line-height:1.2;">PromociÃ³n</strong>
            <button id="promoCloseBtn" type="button" class="pill-btn" style="padding:8px 12px; background:rgba(255,255,255,.12); color:#fff7ed; border-color:rgba(255,255,255,.18);">Cerrar</button>
        </div>
        <div style="display:grid; grid-template-columns:1.05fr .95fr; gap:0;">
            <div class="promo-copy" style="padding:26px 24px; color:#fff7ed; background:radial-gradient(circle at top left, rgba(255,255,255,.08), transparent 22%), linear-gradient(135deg,#201712 0%,#160f0c 48%,#2b1a14 100%);">
                <div style="font-size:11px; letter-spacing:.2em; text-transform:uppercase; color:rgba(255,255,255,.56); margin-bottom:10px;">Promo del dia</div>
                <div id="promoMessage" style="font-size:38px; line-height:.92; font-weight:900; text-transform:uppercase; text-shadow:0 8px 18px rgba(0,0,0,.28);">Nueva promo</div>
                <div id="promoBody" style="margin-top:12px; color:rgba(255,247,237,.82); line-height:1.6; max-width:280px;"></div>
                <div class="promo-actions" style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
                    <button id="promoAcceptBtn" type="button" class="pill-btn primary-link" style="padding:12px 18px;">Ver</button>
                    <button id="promoRejectBtn" type="button" class="pill-btn" style="padding:12px 18px; background:rgba(255,255,255,.12); color:#fff7ed; border-color:rgba(255,255,255,.18);">Cerrar</button>
                </div>
            </div>
            <div class="promo-media" style="padding:18px; display:flex; align-items:center; justify-content:center; background:radial-gradient(circle at center, rgba(255,255,255,.18), transparent 44%), linear-gradient(135deg,#ff7c18 0%,#ff931f 100%);">
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
            <div class="promo-toast-actions" style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px; flex-wrap:wrap;">
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
        bottom: calc(88px + env(safe-area-inset-bottom));
        z-index: 1210;
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

    .pollia-launcher img {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 0 0 2px rgba(255, 248, 236, .72);
    }

    .pollia-widget {
        position: fixed;
        right: 18px;
        bottom: 88px;
        z-index: 1210;
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

    body.cart-panel-open .pollia-launcher,
    body.cart-panel-open .pollia-widget { display: none !important; }

    .pollia-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        background: #050505;
        color: #fffaf0;
    }

    .pollia-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .pollia-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid rgba(255, 207, 58, .42);
        background: #fff8ec;
        box-shadow: 0 10px 22px rgba(0, 0, 0, .24);
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
            bottom: calc(82px + env(safe-area-inset-bottom));
        }

        .pollia-widget {
            right: 10px;
            bottom: 78px;
            width: calc(100vw - 20px);
            height: min(85vh, calc(100vh - 96px));
        }
    }
</style>

<button id="polliaLauncher" type="button" class="pollia-launcher" aria-label="Abrir POLL-IA">
    <img src="/images/ico-pollo.jpg" alt="" aria-hidden="true">
</button>
<section id="polliaWidget" class="pollia-widget" aria-label="Asistente POLL-IA">
    <div class="pollia-head">
        <div class="pollia-brand">
            <img src="/images/ico-pollo.jpg" alt="El Dorado" class="pollia-avatar">
            <div class="pollia-title">
                <strong>POLL-IA</strong>
                <span id="polliaStatus">Chat con El Dorado · Asistente de compras</span>
            </div>
        </div>
        <button id="polliaClose" type="button" class="pollia-close" aria-label="Cerrar">X</button>
    </div>
    <div id="polliaLog" class="pollia-log"></div>
    <div id="polliaPurchaseFlow" class="pollia-purchase-flow" hidden></div>
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
    const purchaseFlow = document.getElementById('polliaPurchaseFlow');

    if (!launcher || !widget || !form || !input || !log) return;

    const guestKey = 'ed_pollia_guest_session';
    const pendingCartKey = 'ed_pollia_pending_cart_v1';
    const checkoutPrefillKey = 'ed_pollia_checkout_prefill_v1';
    let booted = false;
    let sending = false;
    let productsCache = null;

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

        function formatInline(value) {
            return escapeHtml(value).replace(
                /(\/(?:login|register|carrito)(?:\?[A-Za-z0-9._%+\-@=&/%]+)?)/g,
                '<a class="pollia-action-link" href="$1">Continuar</a>'
            );
        }

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
                list.push(formatInline(bullet[1]));
                return;
            }

            flushList();
            const looksLikeHeading = line.length <= 56 && !/[.!?]$/.test(line);
            if (looksLikeHeading) {
                html.push(`<span class="pollia-heading">${formatInline(line)}</span>`);
            } else {
                html.push(`<p>${formatInline(line)}</p>`);
            }
        });

        flushList();
        return html.join('') || '<p>No tengo una respuesta clara todavia.</p>';
    }

    function normalizeProductName(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/kola/g, 'cola')
            .replace(/(\d+)\s*(ml|l)\b/g, '$1$2')
            .replace(/[^a-z0-9/.]+/g, ' ')
            .replace(/\s+/g, ' ');
    }

    async function publicProducts() {
        if (productsCache) return productsCache;
        try {
            const res = await fetch('/api/v1/products', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            productsCache = Array.isArray(data) ? data : [];
        } catch {
            productsCache = [];
        }
        return productsCache;
    }

    const guided = { step: 0, products: [], data: {} };
    function isGuidedPurchaseIntent(value) {
        return /^(ayudame a comprar|quiero hacer un pedido|quiero comprar|ayudame con mi pedido|deseo ordenar|quiero pedir comida)[.!\s]*$/i.test(normalizeProductName(value));
    }
    function categoryText(product) { return normalizeProductName(`${product.category || ''} ${product.name || ''}`); }
    function optionsFor(rows, selected = '') {
        return rows.map(product => `<option value="${Number(product.id)}" ${String(product.id)===String(selected)?'selected':''}>${escapeHtml(product.name)} · S/ ${Number(product.price).toFixed(2)}</option>`).join('');
    }
    function captureGuidedFields() {
        purchaseFlow.querySelectorAll('[data-guided]').forEach(field => { guided.data[field.dataset.guided] = field.type === 'number' ? Number(field.value || 0) : field.value.trim(); });
    }
    async function guidedReverseGeocode(latitude, longitude) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 8000);
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(latitude)}&lon=${encodeURIComponent(longitude)}&zoom=18&addressdetails=1`;
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
            if (!response.ok) throw new Error('No se pudo identificar la calle.');
            return response.json();
        } finally {
            clearTimeout(timeout);
        }
    }
    function requestGuidedLocation() {
        const status = purchaseFlow.querySelector('[data-guided-location-status]');
        if (!navigator.geolocation) {
            if (status) status.textContent = 'Tu navegador no soporta geolocalización. Escribe tu dirección manualmente.';
            return;
        }

        if (status) status.textContent = 'Solicitando permiso y detectando tu ubicación...';
        navigator.geolocation.getCurrentPosition(async position => {
            const latitude = position.coords.latitude.toFixed(7);
            const longitude = position.coords.longitude.toFixed(7);
            guided.data.latitude = latitude;
            guided.data.longitude = longitude;

            const latitudeField = purchaseFlow.querySelector('[data-guided="latitude"]');
            const longitudeField = purchaseFlow.querySelector('[data-guided="longitude"]');
            if (latitudeField) latitudeField.value = latitude;
            if (longitudeField) longitudeField.value = longitude;

            try {
                const data = await guidedReverseGeocode(latitude, longitude);
                const address = data.address || {};
                const road = address.road || address.pedestrian || address.residential || address.cycleway || address.avenue || '';
                const houseNumber = address.house_number || '';
                const suburb = address.suburb || address.neighbourhood || address.city_district || '';
                const city = address.city || address.town || address.village || address.county || '';
                const amenity = address.amenity || address.shop || address.tourism || '';
                const exactPlace = [road, houseNumber].filter(Boolean).join(' ').trim() || data.name || data.display_name || 'Ubicación detectada';
                const nearbyReference = [amenity ? `Cerca de ${amenity}` : '', suburb ? `Zona ${suburb}` : '', city ? `Distrito/Ciudad ${city}` : ''].filter(Boolean).join(' | ');
                guided.data.address = exactPlace;
                guided.data.reference = nearbyReference || 'Ubicación obtenida desde GPS';
                const addressField = purchaseFlow.querySelector('[data-guided="address"]');
                const referenceField = purchaseFlow.querySelector('[data-guided="reference"]');
                if (addressField) addressField.value = guided.data.address;
                if (referenceField) referenceField.value = guided.data.reference;
                if (status) status.textContent = `Ubicación detectada: ${exactPlace}${nearbyReference ? ` | ${nearbyReference}` : ''}`;
            } catch {
                guided.data.address = guided.data.address || 'Ubicación detectada desde GPS';
                const addressField = purchaseFlow.querySelector('[data-guided="address"]');
                if (addressField && !addressField.value.trim()) addressField.value = guided.data.address;
                if (status) status.textContent = 'GPS activado. No pudimos identificar la calle; completa la dirección o referencia manualmente.';
            }
        }, error => {
            const messages = {
                1: 'Permiso de ubicación denegado. Actívalo en el navegador o escribe tu dirección.',
                2: 'No pudimos detectar tu ubicación. Revisa que el GPS esté activado.',
                3: 'La ubicación tardó demasiado. Intenta nuevamente.',
            };
            if (status) status.textContent = messages[error.code] || 'No se pudo obtener tu ubicación.';
        }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
    }
    function closeGuided(cancelled = false) {
        purchaseFlow.hidden = true; purchaseFlow.innerHTML = ''; guided.step = 0; guided.data = {};
        log.style.display = 'flex'; form.style.display = 'flex';
        if (cancelled) addMessage('bot', 'Compra guiada cancelada. Puedes seguir consultándome normalmente.');
    }
    async function openGuidedPurchase() {
        guided.products = (await publicProducts()).filter(product => product && product.can_sell !== false && !product.is_sold_out && product.is_available !== false);
        if (!guided.products.length) { addMessage('bot', 'No hay productos disponibles en este momento.'); return; }
        const user = typeof parseUser === 'function' ? parseUser() : null;
        guided.data = { qty:1, drinkQty:1, deliveryType:'delivery', customerName:user?.name||'', phone:user?.phone||'', email:user?.email||'' };
        guided.step = 0; log.style.display = 'none'; form.style.display = 'none'; purchaseFlow.hidden = false; renderGuidedPurchase();
    }
    function renderGuidedPurchase() {
        const d = guided.data;
        const drinks = guided.products.filter(product => /(bebida|gaseosa|refresco|agua|chicha|limonada|coca|inca|sprite)/.test(categoryText(product)));
        const sides = guided.products.filter(product => /(acompanamiento|guarnicion|papas|arroz|camote|yuca)/.test(categoryText(product)));
        const dishes = guided.products.filter(product => !drinks.includes(product) && !sides.includes(product) && !/ensalada/.test(categoryText(product)));
        const product = guided.products.find(item => String(item.id)===String(d.productId));
        const side = guided.products.find(item => String(item.id)===String(d.sideId));
        const drink = guided.products.find(item => String(item.id)===String(d.drinkId));
        const steps = [
            `<h3>Paso 1 · Plato y cantidad</h3><label>¿Qué plato deseas pedir?<select data-guided="productId"><option value="">Selecciona</option>${optionsFor(dishes,d.productId)}</select></label><label>Cantidad<input data-guided="qty" type="number" min="1" max="20" value="${Number(d.qty)||1}"></label>`,
            `<h3>Paso 2 · Complementos</h3>${sides.length?`<label>Acompañamiento<select data-guided="sideId"><option value="">Sin acompañamiento</option>${optionsFor(sides,d.sideId)}</select></label>`:''}<label>Ensalada<select data-guided="salad"><option value="">Sin ensalada</option><option value="dulce" ${d.salad==='dulce'?'selected':''}>Dulce</option><option value="salada" ${d.salad==='salada'?'selected':''}>Salada</option></select></label>${drinks.length?`<label>Bebida<select data-guided="drinkId"><option value="">Sin bebida</option>${optionsFor(drinks,d.drinkId)}</select></label><label>Cantidad bebida<input data-guided="drinkQty" type="number" min="1" max="20" value="${Number(d.drinkQty)||1}"></label>`:''}<label>Indicaciones<textarea data-guided="notes" maxlength="255" placeholder="Sin ají, papas bien doradas...">${escapeHtml(d.notes||'')}</textarea></label>`,
            `<h3>Paso 3 · Entrega</h3><label>Modalidad<select data-guided="deliveryType"><option value="delivery" ${d.deliveryType==='delivery'?'selected':''}>Delivery</option><option value="pickup" ${d.deliveryType==='pickup'?'selected':''}>Recojo en local</option></select></label><button type="button" data-guided-location>Usar mi ubicación actual</button><span data-guided-location-status>${d.latitude&&d.longitude?'Ubicación GPS guardada. Debajo puedes corregir la dirección.':'El navegador te pedirá permiso para acceder al GPS.'}</span><input data-guided="latitude" type="hidden" value="${escapeHtml(d.latitude||'')}"><input data-guided="longitude" type="hidden" value="${escapeHtml(d.longitude||'')}"><label>Dirección<input data-guided="address" maxlength="255" value="${escapeHtml(d.address||'')}"></label><label>Referencia<input data-guided="reference" maxlength="255" value="${escapeHtml(d.reference||'')}"></label>`,
            `<h3>Paso 4 · Tus datos</h3><label>Nombre completo<input data-guided="customerName" maxlength="120" value="${escapeHtml(d.customerName||'')}"></label><label>Teléfono<input data-guided="phone" inputmode="tel" maxlength="30" value="${escapeHtml(d.phone||'')}"></label><label>Correo<input data-guided="email" type="email" maxlength="120" value="${escapeHtml(d.email||'')}"></label>`,
            `<h3>Resumen</h3><div class="pollia-product-summary"><strong>${escapeHtml(product?.name||'Plato pendiente')} × ${Number(d.qty)||1}</strong>${side?`<span>${escapeHtml(side.name)}</span>`:''}${drink?`<span>${escapeHtml(drink.name)} × ${Number(d.drinkQty)||1}</span>`:''}<span>${d.deliveryType==='delivery'?'Delivery':'Recojo en local'}</span><strong>Subtotal: S/ ${((Number(product?.price||0)*Number(d.qty||1))+Number(side?.price||0)+(Number(drink?.price||0)*Number(d.drinkQty||1))).toFixed(2)}</strong></div>`,
        ];
        const isAuthenticated = Boolean(localStorage.getItem('ed_token'));
        const confirmLabel = isAuthenticated ? 'Agregar al carrito' : 'Continuar para iniciar sesión';
        purchaseFlow.innerHTML = `<div class="pollia-purchase-card">${steps[guided.step]}<div class="pollia-purchase-actions">${guided.step?'<button type="button" data-guided-back>Anterior</button>':''}${guided.step<4?'<button type="button" class="primary" data-guided-next>Continuar</button>':`<button type="button" class="primary" data-guided-confirm>${confirmLabel}</button>`}<button type="button" data-guided-cancel>Cancelar</button></div></div>`;
        purchaseFlow.querySelector('[data-guided-back]')?.addEventListener('click',()=>{captureGuidedFields();guided.step--;renderGuidedPurchase()});
        purchaseFlow.querySelector('[data-guided-location]')?.addEventListener('click', requestGuidedLocation);
        purchaseFlow.querySelector('[data-guided-next]')?.addEventListener('click',()=>{captureGuidedFields();if(guided.step===0&&!d.productId)return alert('Selecciona un plato.');if(guided.step===2&&d.deliveryType==='delivery'&&!d.address)return alert('Ingresa la dirección.');if(guided.step===3&&(!d.customerName||!/^\+?[0-9\s-]{7,30}$/.test(d.phone||'')||!/^\S+@\S+\.\S+$/.test(d.email||'')))return alert('Completa nombre, teléfono y correo válidos.');guided.step++;renderGuidedPurchase()});
        purchaseFlow.querySelector('[data-guided-cancel]')?.addEventListener('click',()=>closeGuided(true));
        purchaseFlow.querySelector('[data-guided-confirm]')?.addEventListener('click', () => {
            captureGuidedFields();
            const main = guided.products.find(item => String(item.id) === String(d.productId));
            if (!main) return;

            const items = [{ ...main, qty: Math.max(1, Number(d.qty) || 1) }];
            if (side) items.push({ ...side, qty: 1 });
            if (drink) items.push({ ...drink, qty: Math.max(1, Number(d.drinkQty) || 1) });

            mergeIntoCart(items);
            localStorage.setItem(checkoutPrefillKey, JSON.stringify({
                customer_name: d.customerName,
                phone: d.phone,
                email: d.email,
                delivery_type: d.deliveryType,
                address: d.deliveryType === 'delivery' ? d.address : '',
                reference: d.reference,
                salad_type: d.salad,
                latitude: d.latitude,
                longitude: d.longitude,
            }));
            sessionStorage.setItem('ed_guided_order_note', String(d.notes || '').slice(0, 120));

            if (localStorage.getItem('ed_token')) {
                closeGuided();
                window.location.href = '/carrito';
                return;
            }

            const email = encodeURIComponent(String(d.email || '').trim());
            const next = encodeURIComponent('/carrito');
            purchaseFlow.innerHTML = `<div class="pollia-purchase-card"><h3>Tu pedido está guardado</h3><div class="pollia-product-summary"><strong>Inicia sesión para continuar</strong><span>Conservaremos los productos, cantidades, dirección y especificaciones que ingresaste.</span><span>Tu correo ya aparecerá escrito en el formulario.</span></div><div class="pollia-purchase-actions"><button type="button" class="primary" data-guided-login>Iniciar sesión</button><button type="button" data-guided-register>Crear cuenta</button><button type="button" data-guided-cancel>Cancelar</button></div></div>`;
            purchaseFlow.querySelector('[data-guided-login]')?.addEventListener('click', () => {
                window.location.href = `/login?email=${email}&next=${next}`;
            });
            purchaseFlow.querySelector('[data-guided-register]')?.addEventListener('click', () => {
                window.location.href = `/register?email=${email}&next=${next}`;
            });
            purchaseFlow.querySelector('[data-guided-cancel]')?.addEventListener('click', () => closeGuided());
        });
    }

    function pendingCart() {
        try {
            const rows = JSON.parse(localStorage.getItem(pendingCartKey) || '[]');
            return Array.isArray(rows) ? rows : [];
        } catch {
            return [];
        }
    }

    function savePendingCart(items) {
        const clean = (items || [])
            .filter((item) => item && Number(item.id) > 0 && Number(item.qty || 0) > 0)
            .map((item) => ({
                id: Number(item.id),
                name: String(item.name || ''),
                category: String(item.category || ''),
                price: Number(item.price || 0),
                qty: Number(item.qty || 1),
                image_url: String(item.image_url || ''),
            }));
        if (!clean.length) {
            localStorage.removeItem(pendingCartKey);
            return;
        }
        localStorage.setItem(pendingCartKey, JSON.stringify(clean));
    }

    function mergePendingCart(items) {
        const next = pendingCart().map((item) => ({ ...item }));
        (items || [])
            .filter((item) => item && Number(item.id) > 0 && Number(item.qty || 0) > 0)
            .forEach((item) => {
                const id = Number(item.id);
                const existing = next.find((row) => Number(row.id) === id);
                const qty = Math.max(1, Math.min(20, Number(item.qty || 1)));
                if (existing) {
                    existing.qty = qty;
                    existing.name = String(item.name || existing.name || '');
                    existing.category = String(item.category || existing.category || '');
                    existing.price = Number(item.price || existing.price || 0);
                    existing.image_url = String(item.image_url || existing.image_url || '');
                    return;
                }
                next.push({
                    id,
                    name: String(item.name || ''),
                    category: String(item.category || ''),
                    price: Number(item.price || 0),
                    qty,
                    image_url: String(item.image_url || ''),
                });
            });
        savePendingCart(next);
    }

    function capturePendingCartFromDraft(draft) {
        if (!draft || typeof draft !== 'object') return false;
        const items = Array.isArray(draft.items) ? draft.items : [];
        savePendingCart(items);
        saveCheckoutPrefill(draft);
        return true;
    }

    function saveCheckoutPrefill(draft) {
        if (!draft || typeof draft !== 'object') return;
        const prefill = {
            customer_name: String(draft.customer_name || ''),
            phone: String(draft.phone || ''),
            email: String(draft.email || ''),
            delivery_type: String(draft.delivery_type || ''),
            address: String(draft.delivery_address || ''),
            reference: String(draft.delivery_reference || ''),
            payment_method: String(draft.payment_method || ''),
            payment_reference: String(draft.payment_reference || ''),
            salad_type: String(draft.salad_type || ''),
            billing_receipt_type: String(draft.billing_receipt_type || ''),
            billing_document_type: String(draft.billing_document_type || ''),
            billing_document_number: String(draft.billing_document_number || ''),
            billing_name: String(draft.billing_name || ''),
        };
        if (!Object.values(prefill).some(Boolean)) return;
        localStorage.setItem(checkoutPrefillKey, JSON.stringify(prefill));
    }

    function mergeIntoCart(items) {
        const cart = JSON.parse(localStorage.getItem('ed_cart') || '[]');
        const next = Array.isArray(cart) ? cart.map((item) => ({ ...item })) : [];
        (items || []).forEach((item) => {
            const id = Number(item.id || 0);
            if (!id) return;
            const existing = next.find((row) => Number(row.id) === id);
            if (existing) {
                existing.qty = Number(existing.qty || 0) + Number(item.qty || 1);
            } else {
                next.push({
                    id,
                    name: item.name,
                    category: item.category || '',
                    price: Number(item.price || 0),
                    qty: Number(item.qty || 1),
                });
            }
        });
        localStorage.setItem('ed_cart', JSON.stringify(next));
        window.dispatchEvent(new Event('storage'));
    }

    async function capturePendingCartFromReply(reply) {
        const text = String(reply || '');
        if (!/(buena eleccion|tu combinacion quedaria|total referencial)/i.test(text)) return;

        const products = await publicProducts();
        if (!products.length) return;

        const byName = new Map(products.map((product) => [normalizeProductName(product.name), product]));
        const items = [];

        text.split(/\r?\n/).forEach((raw) => {
            const line = raw.replace(/^[-*•â€¢\s]+/, '').trim();
            const match = line.match(/^(?:(\d+)\s*x\s*)?(.+?)\s+-\s+S\/\s*\d/i);
            if (!match) return;

            const qty = Math.max(1, Math.min(20, Number(match[1] || 1)));
            const product = byName.get(normalizeProductName(match[2]));
            if (!product || product.can_sell === false || product.is_sold_out) return;

            items.push({
                id: product.id,
                name: product.name,
                category: product.category || '',
                price: Number(product.price || 0),
                qty,
                image_url: product.image_url || '',
            });
        });

        if (items.length) savePendingCart(items);
    }

    function extractEmail(message) {
        const match = String(message || '').match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
        return match ? match[0].toLowerCase() : '';
    }

    async function handleCheckoutEmail(email) {
        const items = pendingCart();
        if (!items.length) return null;

        mergeIntoCart(items);

        const token = localStorage.getItem('ed_token') || '';
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };
        if (token) headers.Authorization = `Bearer ${token}`;

        const res = await fetch('/api/v1/chatbot/cart-intent', {
            method: 'POST',
            headers,
            body: JSON.stringify({
                email,
                guest_session: guestSession(),
                items,
            }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            return data.message || 'No pude guardar esta combinacion. Puedes entrar al carrito y continuar desde ahi: /carrito';
        }
        saveCheckoutPrefill(data.draft);

        const action = data.authenticated
            ? '/carrito'
            : data.registered
                ? (data.login_url || '/login')
                : (data.register_url || '/register');

        return `${data.message || 'Combinacion guardada.'}\n\nContinuar con el pago:\n${action}`;
    }

    async function checkStatus() {
        try {
            const res = await fetch('/api/v1/chatbot/status', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (statusEl) {
                statusEl.textContent = data.ok ? 'Chat con El Dorado · Asistente de compras' : 'Chat con El Dorado · Asistente disponible';
            }
        } catch {
            if (statusEl) statusEl.textContent = 'Chat con El Dorado · Asistente disponible';
        }
    }

    function openWidget() {
        widget.classList.add('open');
        launcher.style.display = 'none';
        if (!booted) {
            booted = true;
            addMessage('bot', 'Hola, soy POLL-IA. Estás en Chat con El Dorado. Escribe “Ayúdame a comprar” para iniciar una compra guiada.');
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
        if (isGuidedPurchaseIntent(message)) {
            addMessage('user', message);
            input.value = '';
            await openGuidedPurchase();
            return;
        }

        sending = true;
        sendBtn.disabled = true;
        addMessage('user', message);
        input.value = '';
        const typing = addMessage('bot', 'Escribiendo...');

        try {
            const checkoutEmail = extractEmail(message);
            if (checkoutEmail && pendingCart().length) {
                const reply = await handleCheckoutEmail(checkoutEmail);
                typing.remove();
                addMessage('bot', reply || 'Guarde tu combinacion en este navegador. Ahora puedes continuar desde el carrito: /carrito');
                return;
            }

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

            const reply = (data.reply || '').toString().trim() || 'No tengo una respuesta clara todavia.';
            addMessage('bot', reply);
            if (!capturePendingCartFromDraft(data.draft)) {
                await capturePendingCartFromReply(reply);
            }
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
    window.addEventListener('ed:customer-session-cleared', () => {
        purchaseFlow.hidden = true;
        purchaseFlow.innerHTML = '';
        purchaseFlow._draft = null;
        log.innerHTML = '';
        log.style.display = 'flex';
        form.style.display = 'flex';
        booted = false;
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
        if (v.startsWith('http://') || v.startsWith('https://')) {
            try {
                const parsed = new URL(v);
                if (parsed.host === window.location.host) return `${window.location.origin}${parsed.pathname}${parsed.search}`;
            } catch {}
            return v;
        }
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
                imageEl.onerror = () => {
                    imageEl.onerror = null;
                    imageEl.src = '/images/products/default.svg';
                };
                imageEl.style.display = 'block';
            } else {
                imageEl.removeAttribute('src');
                imageEl.style.display = 'none';
            }

            acceptBtn.textContent = (p?.cta_label || 'Ver').toString();
            acceptBtn.onclick = () => {
                const destination = (p?.cta_url || (p?.product_id ? `/productos?product=${encodeURIComponent(p.product_id)}` : '/productos')).toString();
                if (destination.startsWith('/') && !destination.startsWith('//')) {
                    window.location.href = destination;
                    return;
                }
                try {
                    const target = new URL(destination, window.location.origin);
                    if (target.protocol === 'http:' || target.protocol === 'https:') window.location.href = target.href;
                } catch {}
            };

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
<script>
(() => {
    const revealSelector = '.page-stack > section, .page-stack > article, .page-stack article, .page-stack img, .page-stack .panel, .page-stack .product-card, .store-footer-grid > section';
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let revealIndex = 0;
    const observer = reducedMotion || !('IntersectionObserver' in window) ? null : new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -5% 0px' });

    function registerRevealElements(root = document) {
        const candidates = [];
        if (root.nodeType === 1 && root.matches?.(revealSelector)) candidates.push(root);
        root.querySelectorAll?.(revealSelector).forEach(element => candidates.push(element));
        candidates.forEach(element => {
            if (element.classList.contains('scroll-reveal')) return;
            element.classList.add('scroll-reveal', revealIndex++ % 2 === 0 ? 'reveal-left' : 'reveal-right');
            if (reducedMotion || !observer) element.classList.add('is-visible');
            else observer.observe(element);
        });
    }

    registerRevealElements();
    const mutationObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => mutation.addedNodes.forEach(node => {
            if (node.nodeType === 1) registerRevealElements(node);
        }));
    });
    const pageStack = document.querySelector('.page-stack');
    if (pageStack) mutationObserver.observe(pageStack, { childList: true, subtree: true });

    async function loadFooterSettings() {
        try {
            const response = await fetch('/api/v1/settings/public');
            const data = await response.json();
            if (!response.ok) return;
            const location = data?.location || {};
            const setText = (id, value) => {
                const element = document.getElementById(id);
                if (element && value) element.textContent = value;
            };
            setText('footerLocationName', location.location_name);
            setText('footerAddress', location.address);
            setText('footerHours', location.business_hours);
            setText('footerPhone', data.support_phone);

            const phone = String(data.support_phone || '').replace(/\D/g, '');
            if (phone) {
                const whatsappUrl = `https://wa.me/${phone}?text=${encodeURIComponent('Hola, deseo información sobre Pollos y Parrillas El Dorado.')}`;
                ['footerWhatsapp', 'footerWhatsappSocial'].forEach(id => {
                    const link = document.getElementById(id);
                    if (link) {
                        link.href = whatsappUrl;
                        link.target = '_blank';
                        link.rel = 'noreferrer';
                    }
                });
            }
        } catch {}
    }

    loadFooterSettings();
})();
</script>
@yield('scripts')
</body>
</html>
