<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <title>Administración </title>
    <style>
        :root {
            --orange: #ff7a1a;
            --orange-soft: #ff9f62;
            --line: rgba(255, 122, 26, .24);
            --text: #ffffff;
            --bg: #0d0d0d;
            --panel: rgba(22, 22, 22, .96);
            --ink-soft: #d1d1d1;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255, 122, 26, .18), transparent 26%),
                radial-gradient(circle at bottom right, rgba(255, 159, 98, .12), transparent 22%),
                linear-gradient(180deg, #060606 0%, #101010 48%, #171717 100%);
            color: var(--text);
        }

        .container { max-width: 1260px; margin: 0 auto; padding: 0 18px; }

        header {
            position: sticky;
            top: 0;
            z-index: 30;
            backdrop-filter: blur(12px);
            background: rgba(255, 252, 248, 0.88);
            border-bottom: 1px solid rgba(240, 207, 179, .92);
            box-shadow: 0 12px 30px rgba(52, 17, 0, .06);
        }

        .head {
            min-height: 84px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 12px 0;
        }

        .head-brand {
            display: grid;
            gap: 4px;
        }

        .head-kicker {
            font-size: 11px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #9b5a2c;
            font-weight: 900;
        }

        .title {
            color: #2d1708;
            font-weight: 900;
            font-size: clamp(20px, 3vw, 30px);
            line-height: 1;
        }

        .title-sub {
            color: var(--ink-soft);
            font-size: 13px;
        }

        .head-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .user {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(234, 182, 138, .82);
            background: rgba(255, 247, 240, .88);
            font-size: 13px;
            color: #8d480f;
            font-weight: 800;
        }

        .logout-btn {
            border-radius: 999px;
            padding: 10px 14px;
        }

        .layout {
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 16px;
            padding: 18px 0 34px;
        }

        .admin-menu {
            position: sticky;
            top: 92px;
            z-index: 29;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            margin-top: 14px;
            border-radius: 18px;
            border: 1px solid rgba(240, 207, 179, .92);
            background: rgba(255, 252, 248, 0.88);
            backdrop-filter: blur(12px);
            box-shadow: 0 12px 30px rgba(52, 17, 0, .06);
        }

        .admin-menu .menu-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .menu-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 999px;
            border: 1px solid rgba(234, 182, 138, .82);
            background: rgba(255, 247, 240, .9);
            color: #7d451e;
            font-weight: 900;
            font-size: 13px;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            cursor: pointer;
        }

        .menu-tab:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 177, 115, .95);
            box-shadow: 0 12px 24px rgba(255, 111, 31, .12);
        }

        .menu-tab.active {
            border-color: rgba(255, 111, 31, .75);
            background: linear-gradient(120deg, rgba(255, 111, 31, .26), rgba(255, 159, 98, .20));
            color: #5a2a08;
        }

        #adminContent.tab-mode { grid-template-columns: 1fr; }
        #adminContent.tab-mode > section { grid-column: 1 / -1; }
        .tab-hidden { display: none !important; }

        .panel {
            background: var(--panel);
            border: 1px solid rgba(240, 207, 179, .92);
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 20px 40px rgba(52, 17, 0, .08);
            backdrop-filter: blur(8px);
        }

        .panel h2, .panel h3 { margin-top: 0; }
        .section-subtitle {
            margin: -4px 0 18px;
            color: var(--ink-soft);
            font-size: 14px;
            line-height: 1.55;
        }
        .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }

        input, select, textarea {
            width: 100%;
            border: 1px solid #edc8a8;
            border-radius: 14px;
            background: #fffdfb;
            color: #2b190f;
            padding: 12px 13px;
            margin-top: 5px;
            margin-bottom: 10px;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #ffb173;
            box-shadow: 0 0 0 4px rgba(255, 111, 31, .10);
            transform: translateY(-1px);
        }

        button {
            border: 1px solid #edc8a8;
            border-radius: 12px;
            background: #fff4ea;
            color: #6c3306;
            cursor: pointer;
            padding: 10px 13px;
            font-weight: 700;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        button:hover {
            transform: translateY(-1px);
            border-color: #ffbb86;
            box-shadow: 0 10px 18px rgba(255, 111, 31, .10);
        }

        .btn-main {
            border: 0;
            background: linear-gradient(120deg, var(--orange), var(--orange-soft));
            color: #2b1406;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(255, 111, 31, .18);
        }

        .list {
            display: grid;
            gap: 12px;
            max-height: 620px;
            overflow: auto;
            padding-right: 3px;
        }

        .card {
            position: relative;
            overflow: hidden;
            border: 1px solid #ffd6ba;
            border-radius: 18px;
            padding: 14px;
            background: linear-gradient(180deg, #fff 0%, #fff8f2 100%);
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }

        .card::after {
            content: "";
            position: absolute;
            inset: auto -40px -40px auto;
            width: 110px;
            height: 110px;
            background: radial-gradient(circle, rgba(255, 111, 31, .11), transparent 68%);
            pointer-events: none;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
        }

        .tag {
            background: #fff0e4;
            border: 1px solid #ffc89d;
            color: #914406;
            border-radius: 999px;
            font-size: 11px;
            padding: 5px 9px;
            text-transform: capitalize;
            font-weight: 800;
        }

        .tag.active { color: #8c4508; background: #fff0e4; }
        .tag.inactive { color: #8a5b3c; background: #fff7f1; }
        .tag.sold-out { color: #a43f07; background: #fff2e8; }
        .tag.stock { background: #fff8f2; }
        .tag.payment-pending { color: #915400; background: #fff3dc; border-color: #ffd295; }
        .tag.payment-reported { color: #8b4304; background: #ffe9d6; border-color: #ffc492; }
        .tag.payment-verified { color: #11663f; background: #e9fff2; border-color: #98dfb7; }
        .tag.payment-rejected { color: #9a2517; background: #ffe5e2; border-color: #ffb8b0; }
        .order-proof-box {
            margin-top: 8px;
            padding: 10px;
            border: 1px dashed #ffd1b0;
            border-radius: 14px;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
        }
        .order-proof-preview {
            display: block;
            width: 100%;
            max-width: 220px;
            max-height: 160px;
            object-fit: contain;
            border-radius: 12px;
            border: 1px solid #ffd8bf;
            background: #fff;
            margin-top: 8px;
        }
        .proof-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(34, 15, 4, .68);
            z-index: 90;
        }
        .proof-modal-card {
            width: 100%;
            max-width: 900px;
            max-height: 88vh;
            overflow: auto;
            border-radius: 20px;
            border: 1px solid #ffd7bd;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
            box-shadow: 0 24px 50px rgba(52, 17, 0, .20);
            padding: 16px;
        }
        .proof-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .proof-modal-title {
            margin: 0;
            color: #8d3d00;
        }
        .proof-modal-body {
            display: grid;
            gap: 12px;
        }
        .proof-modal-image {
            width: 100%;
            max-height: 72vh;
            object-fit: contain;
            border-radius: 14px;
            border: 1px solid #ffd8bf;
            background: #fff;
        }
        .proof-modal-frame {
            width: 100%;
            height: 72vh;
            border: 1px solid #ffd8bf;
            border-radius: 14px;
            background: #fff;
        }

        .img-shell {
            margin-top: 10px;
            margin-bottom: 8px;
            padding: 12px;
            border-radius: 16px;
            border: 1px solid #ffd9bf;
            background:
                radial-gradient(circle at top right, rgba(255, 111, 31, .12), transparent 35%),
                linear-gradient(180deg, #fffdfc 0%, #fff4ea 100%);
        }
        .img-thumb {
            width: 100%;
            aspect-ratio: 1 / 1;
            min-height: 180px;
            max-height: 180px;
            object-fit: contain;
            object-position: center;
            border-radius: 12px;
            background: #fff7f2;
            transition: transform .28s ease, box-shadow .28s ease, filter .28s ease;
        }

        .muted { font-size: 12px; opacity: .78; color: #6e4329; }
        .msg { font-size: 13px; min-height: 20px; }
        .product-form-grid {
            display: grid;
            gap: 14px;
        }
        .toggle-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            border: 1px solid #ffd8bf;
            border-radius: 16px;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
            margin-bottom: 12px;
        }
        .toggle-main {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            color: #65320f;
        }
        .toggle-main input {
            width: 18px;
            height: 18px;
            margin: 0;
            accent-color: #ff6f1f;
        }
        .toggle-status-text {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 7px 12px;
            background: #fff0e4;
            border: 1px solid #ffc89d;
            color: #914406;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .toggle-status-text.inactive {
            background: #fff7f1;
            border-color: #ffd8bf;
            color: #8a5b3c;
        }
        .product-card-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
        }
        .product-card-title {
            margin: 0;
            font-size: 18px;
            color: #27160c;
        }
        .product-card-price {
            margin-top: 6px;
            font-size: 24px;
            font-weight: 900;
            color: #c35300;
        }
        .product-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .product-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .helper-text {
            margin: -4px 0 0;
            color: #8a5b3c;
            font-size: 12px;
        }
        .upload-box {
            display: grid;
            gap: 10px;
            padding: 14px;
            border: 1px dashed #ffc18f;
            border-radius: 18px;
            background: linear-gradient(180deg, #fffdfb 0%, #fff4ea 100%);
        }
        .upload-preview {
            display: none;
            width: 100%;
            max-width: 240px;
            aspect-ratio: 4 / 3;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid #ffd7bd;
            background: #fff;
        }
        .upload-preview.visible {
            display: block;
        }
        .upload-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .dashboard-grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
        .chart-card {
            position: relative;
            overflow: hidden;
            border:1px solid #ffd7bd;
            border-radius:20px;
            padding:14px;
            background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);
        }
        .chart-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 20%, rgba(255,255,255,.3) 50%, transparent 76%);
            transform: translateX(-140%);
            animation: chartSweep 4.4s ease-in-out infinite;
            pointer-events: none;
        }
        .chart-head { display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:8px; }
        .bars { display:grid; grid-template-columns: repeat(auto-fit, minmax(34px, 1fr)); gap:6px; min-height:140px; align-items:end; }
        .bar-col { display:grid; gap:5px; justify-items:center; font-size:10px; color:#7c4219; }
        .bar-fill {
            width:100%;
            max-width:28px;
            border-radius:10px 10px 5px 5px;
            background:linear-gradient(180deg,#ffb071,#ff6f1f);
            box-shadow: 0 8px 14px rgba(255, 111, 31, .18);
            animation: pulseBar 2.8s ease-in-out infinite;
            transform-origin: bottom;
        }

        .card:hover {
            transform: translateY(-3px);
            border-color: #ffbe92;
            box-shadow: 0 14px 26px rgba(255, 111, 31, .10);
        }

        .card:hover .img-thumb {
            transform: scale(1.04);
            filter: saturate(1.05);
            box-shadow: 0 10px 18px rgba(255, 111, 31, .10);
        }

        @keyframes chartSweep {
            0%, 15% { transform: translateX(-140%); }
            45%, 100% { transform: translateX(140%); }
        }

        @keyframes pulseBar {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(1.03); }
        }

        @media (max-width: 980px) {
            .head {
                flex-direction: column;
                align-items: flex-start;
            }
            .head-actions {
                width: 100%;
                justify-content: space-between;
            }
            .layout { grid-template-columns: 1fr; }
            .dashboard-grid { grid-template-columns: 1fr; }
        }

        body, .title, .title-sub, .section-subtitle, .muted, .helper-text, .proof-modal-title, .proof-modal-meta {
            color: #fff;
        }
        header,
        .admin-menu,
        .panel,
        .card,
        .chart-card,
        .proof-modal-card,
        .toggle-row,
        .img-shell,
        .upload-box {
            background: linear-gradient(180deg, rgba(24,24,24,.97) 0%, rgba(15,15,15,.98) 100%) !important;
            border-color: rgba(255, 122, 26, .20) !important;
            box-shadow: 0 18px 40px rgba(0,0,0,.26) !important;
        }
        .menu-tab,
        .user,
        .tag,
        .toggle-status-text,
        button:not(.btn-main) {
            background: rgba(27,27,27,.96) !important;
            color: #fff !important;
            border-color: rgba(255,122,26,.26) !important;
        }
        .menu-tab.active,
        .btn-main {
            color: #111 !important;
        }
        input, select, textarea {
            background: #101010 !important;
            color: #fff !important;
            border-color: rgba(255,122,26,.24) !important;
        }
        .product-card-title,
        .product-card-price,
        .label,
        .head-kicker,
        .section-subtitle,
        .helper-text,
        .muted {
            color: #fff !important;
        }
        .img-thumb,
        .order-proof-preview,
        .proof-modal-image,
        .proof-modal-frame,
        .upload-preview {
            background: #0d0d0d !important;
        }
    </style>
</head>
<body>
<header>
    <div class="container head">
        <div class="head-brand">
            <div class="head-kicker">Centro de Control</div>
            <div class="title">Pollos y Parrillas "El Dorado"</div>
            <div class="title-sub">Productos, pagos, pedidos y ventas en una sola vista operativa.</div>
        </div>
        <div class="head-actions">
            <a href="/admin/dashboard" class="menu-tab">Dashboard ejecutivo</a>
            <div class="user" id="adminUserLabel">Validando sesion...</div>
            <button id="adminLogoutBtn" class="logout-btn">Cerrar sesion</button>
        </div>
    </div>
</header>

<div class="container">
    <div id="denyBox" class="panel" style="display:none; margin-top:16px;">
        <h2>Acceso denegado</h2>
        <p>Necesitas iniciar sesion como administrador.</p>
        <a href="/admin/login" style="color:#ffb387;">Ir a login admin</a>
    </div>

    <nav id="adminMenu" class="admin-menu" style="display:none;">
        <div class="menu-links">
            <button class="menu-tab" type="button" data-target="sec-dashboard">Dashboard</button>
            <button class="menu-tab" type="button" data-target="sec-offers">Promociones</button>
            <button class="menu-tab" type="button" data-target="sec-products">Productos</button>
            <button class="menu-tab" type="button" data-target="sec-orders">Pedidos</button>
            <button class="menu-tab" type="button" data-target="sec-users">Cuentas</button>
        </div>
        <div class="helper-text" style="margin:0;">Panel ordenado por pestañas.</div>
    </nav>

    <div id="adminContent" class="layout" style="display:none;">
        <section id="sec-dashboard" class="panel" style="grid-column: 1 / -1;">
            <h2>Dashboard de ventas</h2>
            <p class="muted">Resumen visual de ventas por dia, mes y año a partir de los pedidos cargados.</p>
            <div id="salesDashboard" class="dashboard-grid">
                <div class="chart-card">
                    <div class="chart-head">
                        <strong>Ventas por dia</strong>
                        <span class="tag">0</span>
                    </div>
                    <div class="muted">Aun sin datos.</div>
                </div>
                <div class="chart-card">
                    <div class="chart-head">
                        <strong>Ventas por mes</strong>
                        <span class="tag">0</span>
                    </div>
                    <div class="muted">Aun sin datos.</div>
                </div>
                <div class="chart-card">
                    <div class="chart-head">
                        <strong>Ventas por año</strong>
                        <span class="tag">0</span>
                    </div>
                    <div class="muted">Aun sin datos.</div>
                </div>
            </div>
        </section>

        <section id="sec-offers" class="panel">
            <h2>Promociones</h2>
            <p class="section-subtitle">La promo se emite en tiempo real a la web y, si Firebase esta configurado, tambien puede salir como push. La imagen se sube desde aqui y se guarda en el servidor.</p>
            <form id="offerForm">
                <div class="row">
                    <div>
                        <label>Destino</label>
                        <select name="target" required>
                            <option value="web">Solo Web</option>
                            <option value="all">Web + App movil</option>
                            <option value="mobile">Solo App movil</option>
                        </select>
                        <div class="helper-text">Mientras la app movil sigue en desarrollo, lo recomendado es usar Solo Web.</div>
                    </div>
                    <div>
                        <label>Boton (opcional)</label>
                        <input name="cta_label" placeholder="Ej: Ver promo">
                    </div>
                </div>
                <div class="toggle-row">
                    <label class="toggle-main">
                        <input type="checkbox" name="send_push" checked> Enviar tambien Push (FCM) para app cerrada
                    </label>
                    <span class="toggle-status-text">Requiere configurar Firebase.</span>
                </div>
                <div class="row">
                    <div>
                        <label>Ti­tulo</label>
                        <input name="title" required maxlength="120" placeholder="Ej: Combo familiar al 20%">
                    </div>
                    <div>
                        <label>Mensaje corto</label>
                        <input name="message" required maxlength="255" placeholder="Ej: Solo hoy, delivery gratis en tu zona">
                    </div>
                </div>
                <label>Contenido (opcional)</label>
                <textarea name="body" rows="3" maxlength="255" placeholder="Describe la promo con mas detalle..."></textarea>
                <div class="upload-box">
                    <label>Imagen de la promocion</label>
                    <input id="offerImageInput" name="image" type="file" accept="image/*">
                    <div class="helper-text">Sube la imagen desde este panel. El sistema la guarda y usa la ruta automaticamente.</div>
                    <img id="offerImagePreview" class="upload-preview" alt="Vista previa de promocion">
                </div>

                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px;">
                    <button type="submit" class="btn-main">Enviar promocion</button>
                </div>
                <div id="offerMsg" class="msg"></div>
            </form>
        </section>

        <section id="sec-products" class="panel">
            <h2>Gestion de Productos</h2>
            <p class="section-subtitle">Administra catalogo, estado visible y stock interno sin mostrar existencias al cliente final.</p>
            <form id="productForm">
                <input type="hidden" name="product_id">
                <div class="product-form-grid">
                <div class="row">
                    <div>
                        <label>Nombre</label>
                        <input name="name" required>
                    </div>
                    <div>
                        <label>Precio (S/)</label>
                        <input name="price" type="number" min="0" step="0.10" required>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Categoria</label>
                        <select name="category" id="categorySelect" required></select>
                    </div>
                    <div>
                        <label>Nueva categoria (opcional)</label>
                        <input id="newCategoryInput" placeholder="Ej: postres">
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Stock interno</label>
                        <input name="stock" type="number" min="0" step="1" value="0" required>
                        <div class="helper-text">Solo visible en admin. Si llega a 0 el cliente vera "Platillo agotado".</div>
                    </div>
                    <div class="upload-box">
                        <label>Imagen del producto</label>
                        <input id="productImageInput" name="image" type="file" accept="image/*">
                        <div class="helper-text">El administrador solo sube la imagen y el sistema conserva la ruta correcta en la base de datos.</div>
                        <img id="productImagePreview" class="upload-preview" alt="Vista previa del producto">
                        <div class="upload-actions">
                            <button type="button" id="removeProductImageBtn">Quitar imagen</button>
                        </div>
                    </div>
                </div>
                <label>Descripcion</label>
                <textarea name="description" rows="3"></textarea>
                <div class="toggle-row">
                    <label class="toggle-main"><input type="checkbox" name="is_available" checked> Visible en la tienda</label>
                    <span id="productStatusText" class="toggle-status-text">Producto activo</span>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="submit" class="btn-main">Guardar producto</button>
                    <button type="button" id="cancelEditBtn">Cancelar edicion</button>
                </div>
                <div id="productMsg" class="msg"></div>
                </div>
            </form>

            <hr style="border-color:#ffd7bd; margin:18px 0;">
            <h3>Lista de productos</h3>
            <p class="section-subtitle">Aqui ves activos, inactivos y agotados, con el stock real para control interno.</p>
            <div id="productsList" class="list"></div>
        </section>

        <section id="sec-orders" class="panel">
            <h2>Pedidos recientes</h2>
            <div class="row">
                <div>
                    <label>Estado pedido</label>
                    <select id="filterStatus">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="confirmed">Confirmado</option>
                        <option value="preparing">Preparando</option>
                        <option value="on_the_way">En camino</option>
                        <option value="delivered">Entregado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
                <div>
                    <label>Metodo de pago</label>
                    <select id="filterPaymentMethod">
                        <option value="">Todos</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                        <option value="transfer">Transferencia</option>
                        <option value="cod">Contraentrega</option>
                        <option value="culqi">Culqi</option>
                    </select>
                </div>
                <div>
                    <label>Estado pago</label>
                    <select id="filterPaymentStatus">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="reported">Reportado</option>
                        <option value="verified">Verificado</option>
                        <option value="rejected">Rechazado</option>
                    </select>
                </div>
                <div>
                    <label>Desde</label>
                    <input id="filterDateFrom" type="date">
                </div>
                <div>
                    <label>Hasta</label>
                    <input id="filterDateTo" type="date">
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-bottom:8px;">
                <button id="applyFiltersBtn">Aplicar filtros</button>
                <button id="clearFiltersBtn">Limpiar</button>
                <button id="exportCsvBtn" class="btn-main">Exportar CSV</button>
            </div>
            <div id="ordersList" class="list"></div>

            <hr style="border-color:#ffd7bd; margin:18px 0;">
            <div class="row" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                <section class="panel" style="padding:16px;">
                    <h3>Actualizar estado</h3>
                    <form id="statusForm">
                        <label>Pedido ID</label>
                        <input name="order_id" required>
                        <label>Nuevo estado</label>
                        <select name="status" required>
                            <option value="pending">Pendiente</option>
                            <option value="confirmed">Confirmado</option>
                            <option value="preparing">Preparando</option>
                            <option value="on_the_way">En camino</option>
                            <option value="delivered">Entregado</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                        <label>Nota (opcional)</label>
                        <input name="note">
                        <button type="submit" class="btn-main">Actualizar estado</button>
                        <div id="statusMsg" class="msg"></div>
                    </form>
                </section>

                <section class="panel" style="padding:16px;">
                    <h3>Validar pago QR/transferencia</h3>
                    <form id="paymentForm">
                        <label>Pedido ID</label>
                        <input name="order_id" required>
                        <label>Estado de pago</label>
                        <select name="payment_status" required>
                            <option value="pending">Pendiente</option>
                            <option value="reported">Reportado</option>
                            <option value="verified">Verificado</option>
                            <option value="rejected">Rechazado</option>
                        </select>
                        <label>Codigo operacion (opcional)</label>
                        <input name="payment_reference">
                        <label>Nota (opcional)</label>
                        <input name="note">
                        <button type="submit" class="btn-main">Actualizar pago</button>
                        <div id="paymentMsg" class="msg"></div>
                    </form>
                </section>
            </div>
        </section>

        <section id="sec-users" class="panel" style="grid-column: 1 / -1;">
            <h2>Gestion de cuentas</h2>
            <p class="muted">Controla cuentas registradas, tiempo de creación y estado activo.</p>
            <div id="usersList" class="list"></div>
        </section>
    </div>
</div>

<div id="proofModal" class="proof-modal">
    <div class="proof-modal-card">
        <div class="proof-modal-head">
            <h3 id="proofModalTitle" class="proof-modal-title">Comprobante</h3>
            <button id="proofModalCloseBtn" type="button">Cerrar</button>
        </div>
        <div class="proof-modal-body">
            <div id="proofModalMeta" class="muted"></div>
            <div id="proofModalContent"></div>
        </div>
    </div>
</div>

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

const denyBox = document.getElementById('denyBox');
const adminContent = document.getElementById('adminContent');
const adminUserLabel = document.getElementById('adminUserLabel');
const adminLogoutBtn = document.getElementById('adminLogoutBtn');
const adminMenu = document.getElementById('adminMenu');
const adminMenuTabs = Array.from(document.querySelectorAll('#adminMenu .menu-tab'));
const adminSections = [
    document.getElementById('sec-dashboard'),
    document.getElementById('sec-offers'),
    document.getElementById('sec-products'),
    document.getElementById('sec-orders'),
    document.getElementById('sec-users'),
].filter(Boolean);

function showAdminTab(targetId) {
    adminSections.forEach(section => {
        section.classList.toggle('tab-hidden', section.id !== targetId);
    });
    adminMenuTabs.forEach(tab => {
        tab.classList.toggle('active', tab.dataset.target === targetId);
    });
    if (adminContent) adminContent.classList.add('tab-mode');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

const productForm = document.getElementById('productForm');
const productMsg = document.getElementById('productMsg');
const categorySelect = document.getElementById('categorySelect');
const newCategoryInput = document.getElementById('newCategoryInput');
const cancelEditBtn = document.getElementById('cancelEditBtn');
const productsList = document.getElementById('productsList');
const productStatusText = document.getElementById('productStatusText');
const productImageInput = document.getElementById('productImageInput');
const productImagePreview = document.getElementById('productImagePreview');
const removeProductImageBtn = document.getElementById('removeProductImageBtn');

const offerForm = document.getElementById('offerForm');
const offerMsg = document.getElementById('offerMsg');
const offerImageInput = document.getElementById('offerImageInput');
const offerImagePreview = document.getElementById('offerImagePreview');

const statusForm = document.getElementById('statusForm');
const statusMsg = document.getElementById('statusMsg');
const paymentForm = document.getElementById('paymentForm');
const paymentMsg = document.getElementById('paymentMsg');
const ordersList = document.getElementById('ordersList');
const filterStatus = document.getElementById('filterStatus');
const filterPaymentMethod = document.getElementById('filterPaymentMethod');
const filterPaymentStatus = document.getElementById('filterPaymentStatus');
const filterDateFrom = document.getElementById('filterDateFrom');
const filterDateTo = document.getElementById('filterDateTo');
const applyFiltersBtn = document.getElementById('applyFiltersBtn');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');
const exportCsvBtn = document.getElementById('exportCsvBtn');
const usersList = document.getElementById('usersList');
const salesDashboard = document.getElementById('salesDashboard');
const proofModal = document.getElementById('proofModal');
const proofModalTitle = document.getElementById('proofModalTitle');
const proofModalMeta = document.getElementById('proofModalMeta');
const proofModalContent = document.getElementById('proofModalContent');
const proofModalCloseBtn = document.getElementById('proofModalCloseBtn');

const BASE_CATEGORIES = ['pollos', 'parrillas', 'bebidas'];
const ADMIN_TIMEOUT_MS = 30 * 60 * 1000;
let productsCache = [];
let refreshTimer = null;
let productImageRemoved = false;

const STATUS_ES = {
    pending: 'Pendiente',
    confirmed: 'Confirmado',
    preparing: 'Preparando',
    on_the_way: 'En camino',
    delivered: 'Entregado',
    cancelled: 'Cancelado',
};

const PAYMENT_STATUS_ES = {
    pending: 'Pendiente',
    reported: 'Reportado',
    verified: 'Verificado',
    rejected: 'Rechazado',
};

function getToken() { return localStorage.getItem('ed_token'); }
function getUser() {
    const raw = localStorage.getItem('ed_user');
    if (!raw) return null;
    try { return JSON.parse(raw); } catch { return null; }
}

function setUploadPreview(previewEl, value) {
    if (!previewEl) return;
    if (!value) {
        previewEl.removeAttribute('src');
        previewEl.classList.remove('visible');
        return;
    }
    previewEl.src = value;
    previewEl.classList.add('visible');
}

function bindImagePreview(inputEl, previewEl) {
    if (!inputEl || !previewEl) return;
    inputEl.addEventListener('change', () => {
        const file = inputEl.files?.[0];
        if (!file) {
            if (!previewEl.dataset.persisted) setUploadPreview(previewEl, '');
            return;
        }
        previewEl.dataset.persisted = '';
        setUploadPreview(previewEl, URL.createObjectURL(file));
    });
}

async function sendOffer(formData, targetValue) {
    const token = getToken();
    offerMsg.textContent = 'Enviando...';
    offerMsg.classList.remove('success');
    offerMsg.classList.remove('error');

    const res = await fetch('/api/v1/admin/notifications/offers', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: formData,
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        offerMsg.textContent = data?.message || 'No se pudo enviar la promo.';
        offerMsg.classList.add('error');
        return;
    }

    const pushStatus = data?.push?.ok === true
        ? ` Push: OK (${data.push.topic})`
        : (data?.push?.ok === false ? ` Push: ${data.push.message || 'ERROR'}` : '');

    offerMsg.textContent = `OK. Enviada a canal ${data.channel} (${targetValue || 'all'}).${pushStatus}`;
    offerMsg.classList.add('success');
    offerForm.reset();
    setUploadPreview(offerImagePreview, '');
}

function parseSession() {
    const raw = localStorage.getItem('ed_session');
    if (!raw) return null;
    try { return JSON.parse(raw); } catch { return null; }
}

function saveSession(session) {
    localStorage.setItem('ed_session', JSON.stringify(session));
}

function touchAdminSession() {
    const session = parseSession();
    if (!session || session.role !== 'admin') return;
    session.lastActivity = Date.now();
    session.expiresAt = Date.now() + ADMIN_TIMEOUT_MS;
    saveSession(session);
}

function clearAuth() {
    localStorage.removeItem('ed_token');
    localStorage.removeItem('ed_user');
    localStorage.removeItem('ed_session');
}

function statusEs(code) {
    return STATUS_ES[code] || code || 'n/a';
}

function paymentStatusEs(code) {
    return PAYMENT_STATUS_ES[code] || code || 'n/a';
}

function paymentStatusClass(code) {
    const normalized = String(code || '').toLowerCase();
    return {
        pending: 'payment-pending',
        reported: 'payment-reported',
        verified: 'payment-verified',
        rejected: 'payment-rejected',
    }[normalized] || '';
}

function isImageProof(path) {
    return /\.(jpg|jpeg|png|webp)$/i.test(String(path || ''));
}

function isPdfProof(path) {
    return /\.pdf$/i.test(String(path || ''));
}

function closeProofModal() {
    proofModal.style.display = 'none';
    proofModalMeta.textContent = '';
    proofModalContent.innerHTML = '';
}

function openProofModal(order) {
    if (!order?.payment_proof_path) return;

    proofModalTitle.textContent = `Comprobante ${order.tracking_code}`;
    proofModalMeta.textContent = `Pedido ${order.id} | ${order.customer_name} | ${order.payment_method || 'n/a'} | ${paymentStatusEs(order.payment_status)}`;

    if (isImageProof(order.payment_proof_path)) {
        proofModalContent.innerHTML = `
            <img src="${order.payment_proof_path}" alt="Comprobante ${order.tracking_code}" class="proof-modal-image">
            <div><a href="${order.payment_proof_path}" target="_blank">Abrir archivo en otra pestaña</a></div>
        `;
    } else if (isPdfProof(order.payment_proof_path)) {
        proofModalContent.innerHTML = `
            <iframe src="${order.payment_proof_path}" class="proof-modal-frame"></iframe>
            <div><a href="${order.payment_proof_path}" target="_blank">Abrir PDF en otra pestaña</a></div>
        `;
    } else {
        proofModalContent.innerHTML = `
            <div class="muted">No hay vista previa integrada para este archivo.</div>
            <div><a href="${order.payment_proof_path}" target="_blank">Abrir archivo</a></div>
        `;
    }

    proofModal.style.display = 'flex';
}

function money(value) {
    return `S/ ${Number(value || 0).toFixed(2)}`;
}

function canUseAdmin() {
    const user = getUser();
    return Boolean(user && user.role === 'admin' && getToken());
}

function upsertCategoryOptions() {
    const categories = new Set(BASE_CATEGORIES);
    productsCache.forEach(product => {
        if (product.category) categories.add(String(product.category).toLowerCase());
    });

    const selected = categorySelect.value;
    categorySelect.innerHTML = [...categories]
        .sort()
        .map(category => `<option value="${category}">${category}</option>`)
        .join('');

    if (selected && [...categories].includes(selected)) categorySelect.value = selected;
}

function clearProductForm() {
    productForm.reset();
    productForm.product_id.value = '';
    productForm.is_available.checked = true;
    productForm.stock.value = 0;
    newCategoryInput.value = '';
    productImageRemoved = false;
    if (productImagePreview) productImagePreview.dataset.persisted = '';
    setUploadPreview(productImagePreview, '');
    syncProductAvailabilityLabel();
}

function productStatusMeta(product) {
    if (!product.is_available) {
        return { text: 'Producto inactivo', className: 'inactive' };
    }

    if (Number(product.stock || 0) <= 0) {
        return { text: 'Platillo agotado', className: 'sold-out' };
    }

    return { text: 'Producto activo', className: 'active' };
}

function productCard(product) {
    const image = product.image_url || '/images/products/default.svg';
    const status = productStatusMeta(product);
    return `
        <article class="card">
            <div class="product-card-header">
                <div>
                    <h4 class="product-card-title">${product.name}</h4>
                    <div class="product-card-price">S/ ${Number(product.price).toFixed(2)}</div>
                </div>
                <span class="tag">${product.category || 'general'}</span>
            </div>
            <div class="img-shell">
                <img src="${image}" alt="${product.name}" class="img-thumb">
            </div>
            <div class="product-chip-row">
                <span class="tag ${status.className}">${status.text}</span>
                <span class="tag stock">Stock: ${Number(product.stock || 0)}</span>
                <span class="tag">ID ${product.id}</span>
            </div>
            <div class="muted">${product.description || 'Sin descripcion'}</div>
            <div class="product-card-actions">
                <button data-edit="${product.id}">Editar</button>
                <button data-delete="${product.id}" style="border-color:#ffc1b5; color:#a53216;">Eliminar</button>
            </div>
        </article>`;
}

function formatBucketLabel(date, mode) {
    if (mode === 'day') {
        return date.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit' });
    }
    if (mode === 'month') {
        return date.toLocaleDateString('es-PE', { month: 'short', year: '2-digit' }).replace('.', '');
    }
    return String(date.getFullYear());
}

function buildBuckets(orders, mode) {
    const map = new Map();
    orders.forEach(order => {
        const sourceDate = new Date(order.created_at);
        if (Number.isNaN(sourceDate.getTime())) return;
        const key = mode === 'day'
            ? sourceDate.toISOString().slice(0, 10)
            : mode === 'month'
                ? `${sourceDate.getFullYear()}-${String(sourceDate.getMonth() + 1).padStart(2, '0')}`
                : `${sourceDate.getFullYear()}`;
        const current = map.get(key) || { total: 0, count: 0, date: sourceDate };
        current.total += Number(order.total_amount || 0);
        current.count += 1;
        current.date = sourceDate;
        map.set(key, current);
    });

    return [...map.entries()]
        .sort((a, b) => a[0].localeCompare(b[0]))
        .slice(-6)
        .map(([, value]) => ({
            label: formatBucketLabel(value.date, mode),
            total: value.total,
            count: value.count,
        }));
}

function renderChart(title, rows) {
    if (!rows.length) {
        return `
            <div class="chart-card">
                <div class="chart-head">
                    <strong>${title}</strong>
                    <span class="tag">0</span>
                </div>
                <div class="muted">Sin pedidos para mostrar.</div>
            </div>`;
    }

    const max = Math.max(...rows.map(item => item.total), 1);

    return `
        <div class="chart-card">
            <div class="chart-head">
                <strong>${title}</strong>
                <span class="tag">${money(rows.reduce((sum, item) => sum + item.total, 0))}</span>
            </div>
            <div class="bars">
                ${rows.map(item => `
                    <div class="bar-col" title="${item.label}: ${money(item.total)} en ${item.count} pedidos">
                        <div class="bar-fill" style="height:${Math.max(16, Math.round((item.total / max) * 110))}px;"></div>
                        <strong>${item.label}</strong>
                        <span>${money(item.total)}</span>
                    </div>
                `).join('')}
            </div>
        </div>`;
}

function renderDashboard(stats) {
    if (!salesDashboard) return;
    const dayRows = stats?.buckets?.day || [];
    const monthRows = stats?.buckets?.month || [];
    const yearRows = stats?.buckets?.year || [];
    const payments = stats?.payments || [];
    const summary = stats?.summary || {};
    const bestDay = summary.best_day;
    const worstDay = summary.worst_day;

    salesDashboard.innerHTML = [
        renderChart('Ventas por dia', dayRows.slice(-6).map(item => ({
            label: item.label,
            total: Number(item.total || 0),
            count: Number(item.count || 0),
        }))),
        renderChart('Ventas por mes', monthRows.slice(-6).map(item => ({
            label: item.label,
            total: Number(item.total || 0),
            count: Number(item.count || 0),
        }))),
        renderChart('Ventas por ano', yearRows.slice(-6).map(item => ({
            label: item.label,
            total: Number(item.total || 0),
            count: Number(item.count || 0),
        }))),
        `
        <div class="chart-card">
            <div class="chart-head">
                <strong>Indicadores utiles</strong>
                <span class="tag">${Number(summary.orders_count || 0)} pedidos</span>
            </div>
            <div class="muted">Venta total: <strong>S/ ${money(summary.total_sales || 0)}</strong></div>
            <div class="muted">Ticket promedio: <strong>S/ ${money(summary.average_ticket || 0)}</strong></div>
            <div class="muted">Mejor dia: <strong>${bestDay ? `${bestDay.label} | S/ ${money(bestDay.total)}` : 'Sin datos'}</strong></div>
            <div class="muted">Dia mas bajo: <strong>${worstDay ? `${worstDay.label} | S/ ${money(worstDay.total)}` : 'Sin datos'}</strong></div>
        </div>`,
        `
        <div class="chart-card">
            <div class="chart-head">
                <strong>Pagos digitales</strong>
                <span class="tag">${payments.length}</span>
            </div>
            ${payments.length ? payments.map(payment => `
                <div class="muted" style="margin-bottom:6px;">
                    <strong>${payment.method}</strong>: S/ ${money(payment.total || 0)} | ${payment.count} pedidos
                    <br>Verificados: ${payment.verified_count} | Reportados: ${payment.reported_count} | Pendientes: ${payment.pending_count}
                </div>
            `).join('') : '<div class="muted">Sin datos de pago.</div>'}
        </div>`,
    ].join('');
}

async function fetchOrderStats() {
    const token = getToken();
    const params = new URLSearchParams();
    if (filterStatus.value) params.set('status', filterStatus.value);
    if (filterPaymentMethod.value) params.set('payment_method', filterPaymentMethod.value);
    if (filterPaymentStatus.value) params.set('payment_status', filterPaymentStatus.value);
    if (filterDateFrom.value) params.set('date_from', filterDateFrom.value);
    if (filterDateTo.value) params.set('date_to', filterDateTo.value);
    const query = params.toString() ? `?${params.toString()}` : '';

    const res = await fetch(`/api/v1/admin/orders/stats${query}`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        renderDashboard(null);
        return;
    }
    renderDashboard(data);
}

async function fetchProducts() {
    const res = await fetch('/api/v1/admin/products', {
        headers: { 'Authorization': `Bearer ${getToken()}` },
    });
    const data = await res.json();
    productsCache = Array.isArray(data) ? data : [];
    upsertCategoryOptions();
    renderProducts();
}

function renderProducts() {
    if (!productsCache.length) {
        productsList.innerHTML = '<div class="card">No hay productos.</div>';
        return;
    }
    productsList.innerHTML = productsCache.map(productCard).join('');

    productsList.querySelectorAll('[data-edit]').forEach(btn => {
        btn.addEventListener('click', () => editProduct(Number(btn.getAttribute('data-edit'))));
    });
    productsList.querySelectorAll('[data-delete]').forEach(btn => {
        btn.addEventListener('click', () => deleteProduct(Number(btn.getAttribute('data-delete'))));
    });
}

function editProduct(productId) {
    const product = productsCache.find(item => item.id === productId);
    if (!product) return;
    productForm.product_id.value = product.id;
    productForm.name.value = product.name || '';
    productForm.price.value = product.price || '';
    productForm.description.value = product.description || '';
    productForm.is_available.checked = Boolean(product.is_available);
    productForm.stock.value = Number(product.stock || 0);
    categorySelect.value = String(product.category || 'pollos').toLowerCase();
    productImageRemoved = false;
    if (productImageInput) productImageInput.value = '';
    if (productImagePreview) productImagePreview.dataset.persisted = product.image_url || '';
    setUploadPreview(productImagePreview, product.image_url || '');
    syncProductAvailabilityLabel();
    productMsg.textContent = `Editando producto ID ${product.id}`;
}

async function deleteProduct(productId) {
    if (!confirm(`Eliminar producto ID ${productId}?`)) return;
    const token = getToken();
    const res = await fetch(`/api/v1/products/${productId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        productMsg.textContent = data.message || 'No se pudo eliminar';
        return;
    }
    productMsg.textContent = 'Producto eliminado';
    await fetchProducts();
}

async function saveProduct(e) {
    e.preventDefault();
    const token = getToken();

    const customCategory = newCategoryInput.value.trim().toLowerCase();
    const category = customCategory || categorySelect.value;
    if (!category) {
        productMsg.textContent = 'Selecciona o crea una categoria';
        return;
    }

    const formData = new FormData();
    formData.append('name', productForm.name.value.trim());
    formData.append('price', String(Number(productForm.price.value)));
    formData.append('category', category);
    formData.append('description', productForm.description.value.trim() || '');
    formData.append('is_available', productForm.is_available.checked ? '1' : '0');
    formData.append('stock', String(Number(productForm.stock.value || 0)));
    if (productImageRemoved) formData.append('remove_image', '1');
    if (productImageInput?.files?.[0]) formData.append('image', productImageInput.files[0]);

    const editingId = productForm.product_id.value.trim();
    const url = editingId ? `/api/v1/products/${editingId}` : '/api/v1/products';
    const method = editingId ? 'PUT' : 'POST';
    if (editingId) formData.append('_method', 'PUT');

    const res = await fetch(url, {
        method: editingId ? 'POST' : method,
        headers: {
            'Authorization': `Bearer ${token}`,
        },
        body: formData,
    });
    const raw = await res.text();
    let data = {};
    try {
        data = raw ? JSON.parse(raw) : {};
    } catch {
        data = { message: raw || 'Respuesta invalida del servidor' };
    }

    if (!res.ok) {
        const validationErrors = data.errors ? Object.values(data.errors).flat().join(' | ') : '';
        productMsg.textContent = validationErrors || data.message || 'No se pudo guardar el producto';
        return;
    }

    productMsg.textContent = editingId ? 'Producto actualizado' : 'Producto creado';
    clearProductForm();
    await fetchProducts();
}

function syncProductAvailabilityLabel() {
    if (!productStatusText) return;
    const active = Boolean(productForm.is_available.checked);
    productStatusText.textContent = active ? 'Producto activo' : 'Producto inactivo';
    productStatusText.className = `toggle-status-text${active ? '' : ' inactive'}`;
}

async function fetchOrders() {
    const token = getToken();
    const params = new URLSearchParams();
    if (filterStatus.value) params.set('status', filterStatus.value);
    if (filterPaymentMethod.value) params.set('payment_method', filterPaymentMethod.value);
    if (filterPaymentStatus.value) params.set('payment_status', filterPaymentStatus.value);
    if (filterDateFrom.value) params.set('date_from', filterDateFrom.value);
    if (filterDateTo.value) params.set('date_to', filterDateTo.value);
    const query = params.toString() ? `?${params.toString()}` : '';

    const res = await fetch(`/api/v1/admin/orders${query}`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        renderDashboard(null);
        ordersList.innerHTML = '<div class="card">No se pudieron cargar pedidos.</div>';
        return;
    }

    const orders = data.data || [];
    if (!orders.length) {
        ordersList.innerHTML = '<div class="card">Sin pedidos recientes.</div>';
    } else {
        ordersList.innerHTML = orders.map(order => `
            <article class="card">
                <div class="card-top">
                    <strong>${order.tracking_code}</strong>
                    <span class="tag">${statusEs(order.status)}</span>
                </div>
                <div class="muted">ID: ${order.id} | ${order.customer_name}</div>
                <div class="muted">Fecha/Hora: ${new Date(order.created_at).toLocaleString()}</div>
                <div class="muted">
                    Pago: ${order.payment_method || 'n/a'}
                    <span class="tag ${paymentStatusClass(order.payment_status)}" style="margin-left:6px;">${paymentStatusEs(order.payment_status)}</span>
                </div>
                <div class="muted">Operacion: ${order.payment_reference || 'sin codigo'}</div>
                <div class="order-proof-box">
                    <div class="muted">Comprobante: ${order.payment_proof_path ? `<a href="${order.payment_proof_path}" target="_blank">Ver archivo</a>` : 'no subido'}</div>
                    ${order.payment_proof_path && isImageProof(order.payment_proof_path)
                        ? `<img src="${order.payment_proof_path}" alt="Comprobante ${order.tracking_code}" class="order-proof-preview">`
                        : ''}
                </div>
                <div style="margin-top:6px;">Total: <strong>S/ ${Number(order.total_amount).toFixed(2)}</strong></div>
                <div style="display:flex; gap:8px; margin-top:8px;">
                    <button data-fill="${order.id}">Usar en actualizar estado</button>
                    ${order.payment_proof_path ? `<button data-proof-modal="${order.id}">Ver comprobante</button>` : ''}
                    <button data-delete-order="${order.id}" style="border-color:#ffc1b5; color:#a53216;">Eliminar pedido</button>
                </div>
            </article>
        `).join('');
    }
    await fetchOrderStats();

    ordersList.querySelectorAll('[data-fill]').forEach(btn => {
        btn.addEventListener('click', () => {
            statusForm.order_id.value = btn.getAttribute('data-fill');
            paymentForm.order_id.value = btn.getAttribute('data-fill');
            statusMsg.textContent = `Pedido ID ${btn.getAttribute('data-fill')} seleccionado`;
            paymentMsg.textContent = `Pedido ID ${btn.getAttribute('data-fill')} seleccionado`;
        });
    });
    ordersList.querySelectorAll('[data-proof-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const order = orders.find(item => item.id === Number(btn.getAttribute('data-proof-modal')));
            openProofModal(order);
        });
    });
    ordersList.querySelectorAll('[data-delete-order]').forEach(btn => {
        btn.addEventListener('click', () => deleteOrder(Number(btn.getAttribute('data-delete-order'))));
    });
}

async function exportCsv() {
    const token = getToken();
    const params = new URLSearchParams();
    if (filterStatus.value) params.set('status', filterStatus.value);
    if (filterPaymentMethod.value) params.set('payment_method', filterPaymentMethod.value);
    if (filterPaymentStatus.value) params.set('payment_status', filterPaymentStatus.value);
    if (filterDateFrom.value) params.set('date_from', filterDateFrom.value);
    if (filterDateTo.value) params.set('date_to', filterDateTo.value);
    const query = params.toString() ? `?${params.toString()}` : '';

    const res = await fetch(`/api/v1/admin/orders/export${query}`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    if (!res.ok) {
        statusMsg.textContent = 'No se pudo exportar CSV';
        return;
    }
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'pedidos-admin.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}

async function fetchUsers() {
    const token = getToken();
    const res = await fetch('/api/v1/admin/users', {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        usersList.innerHTML = '<div class="card">No se pudieron cargar usuarios.</div>';
        return;
    }

    const users = data.data || [];
    if (!users.length) {
        usersList.innerHTML = '<div class="card">Sin cuentas registradas.</div>';
        return;
    }

    usersList.innerHTML = users.map(user => {
        const days = Math.floor((Date.now() - new Date(user.created_at).getTime()) / (1000 * 60 * 60 * 24));
        return `
            <article class="card">
                <div class="card-top">
                    <strong>${user.name}</strong>
                    <span class="tag">${user.role}</span>
                </div>
                <div class="muted">${user.email}</div>
                <div class="muted">Telefono: ${user.phone || '-'}</div>
                <div class="muted">Creada: ${new Date(user.created_at).toLocaleString()} (${days} dias)</div>
                <div class="muted">Estado: ${user.is_active ? 'Activa' : 'Desactivada'}</div>
                <div style="display:flex; gap:8px; margin-top:8px;">
                    <button data-toggle-user="${user.id}" data-next="${user.is_active ? '0' : '1'}">
                        ${user.is_active ? 'Dar de baja' : 'Reactivar'}
                    </button>
                    <button data-delete-user="${user.id}" style="border-color:#ffc1b5; color:#a53216;">Eliminar</button>
                </div>
            </article>`;
    }).join('');

    usersList.querySelectorAll('[data-toggle-user]').forEach(btn => {
        btn.addEventListener('click', () => toggleUserActive(
            Number(btn.getAttribute('data-toggle-user')),
            btn.getAttribute('data-next') === '1'
        ));
    });

    usersList.querySelectorAll('[data-delete-user]').forEach(btn => {
        btn.addEventListener('click', () => deleteUser(Number(btn.getAttribute('data-delete-user'))));
    });
}

async function toggleUserActive(userId, isActive) {
    const token = getToken();
    const res = await fetch(`/api/v1/admin/users/${userId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({ is_active: isActive }),
    });
    const data = await res.json();
    if (!res.ok) {
        statusMsg.textContent = data.message || 'No se pudo actualizar cuenta';
        return;
    }
    statusMsg.textContent = `Cuenta ${data.name} ${data.is_active ? 'activada' : 'desactivada'}`;
    await fetchUsers();
}

async function deleteUser(userId) {
    if (!confirm(`Eliminar usuario ID ${userId}?`)) return;
    const token = getToken();
    const res = await fetch(`/api/v1/admin/users/${userId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        statusMsg.textContent = data.message || 'No se pudo eliminar usuario';
        return;
    }
    statusMsg.textContent = `Usuario ${userId} eliminado`;
    await fetchUsers();
}

async function updateOrderStatus(e) {
    e.preventDefault();
    const token = getToken();
    const orderId = statusForm.order_id.value.trim();
    if (!orderId) return;

    const payload = {
        status: statusForm.status.value,
        note: statusForm.note.value.trim() || null,
    };

    const res = await fetch(`/api/v1/admin/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok) {
        statusMsg.textContent = data.message || 'No se pudo actualizar estado';
        return;
    }
    statusMsg.textContent = `Estado actualizado a ${statusEs(data.status)}`;
    await fetchOrders();
}

async function updatePaymentStatus(e) {
    e.preventDefault();
    const token = getToken();
    const orderId = paymentForm.order_id.value.trim();
    if (!orderId) return;

    const payload = {
        payment_status: paymentForm.payment_status.value,
        payment_reference: paymentForm.payment_reference.value.trim() || null,
        note: paymentForm.note.value.trim() || null,
    };

    const res = await fetch(`/api/v1/admin/orders/${orderId}/payment-status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok) {
        paymentMsg.textContent = data.message || 'No se pudo actualizar pago';
        return;
    }
    paymentMsg.textContent = `Pago actualizado a ${paymentStatusEs(data.payment_status)}`;
    await fetchOrders();
}

async function deleteOrder(orderId) {
    if (!confirm(`Eliminar pedido ID ${orderId}? Esta accion lo quitara de la vista del cliente.`)) return;
    const token = getToken();
    const res = await fetch(`/api/v1/admin/orders/${orderId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await res.json();
    if (!res.ok) {
        statusMsg.textContent = data.message || 'No se pudo eliminar pedido';
        return;
    }
    statusMsg.textContent = `Pedido ${orderId} eliminado`;
    paymentMsg.textContent = `Pedido ${orderId} eliminado`;
    await fetchOrders();
}

async function boot() {
    const user = getUser();
    const session = parseSession();
    if (!canUseAdmin() || !session || session.role !== 'admin' || Date.now() > Number(session.expiresAt || 0)) {
        clearAuth();
        denyBox.style.display = 'block';
        adminUserLabel.textContent = 'Sin permisos admin';
        setTimeout(() => { window.location.href = '/admin/login'; }, 800);
        return;
    }

    try {
        const meRes = await fetch('/api/v1/auth/me', {
            headers: { 'Authorization': `Bearer ${getToken()}` },
        });
        const meData = await meRes.json();
        if (!meRes.ok || !meData.user || meData.user.role !== 'admin' || !meData.user.is_active) {
            clearAuth();
            window.location.href = '/admin/login';
            return;
        }
        localStorage.setItem('ed_user', JSON.stringify(meData.user));
    } catch {
        clearAuth();
        window.location.href = '/admin/login';
        return;
    }

    touchAdminSession();
    adminUserLabel.textContent = `Admin: ${(getUser() || user).name}`;
    adminContent.style.display = 'grid';
    if (adminMenu) adminMenu.style.display = 'flex';
    showAdminTab('sec-dashboard');

    upsertCategoryOptions();
    await fetchProducts();
    await fetchOrders();
    await fetchUsers();

    refreshTimer = setInterval(async () => {
        if (Date.now() > Number((parseSession() || {}).expiresAt || 0)) {
            clearAuth();
            window.location.href = '/admin/login';
            return;
        }
        await fetchProducts();
        await fetchOrders();
        await fetchUsers();
    }, 20000);
}

cancelEditBtn.addEventListener('click', clearProductForm);
productForm.is_available.addEventListener('change', syncProductAvailabilityLabel);
productForm.addEventListener('submit', saveProduct);
if (offerForm) {
    offerForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData();
        formData.append('target', offerForm.target.value);
        formData.append('send_push', offerForm.send_push?.checked ? '1' : '0');
        formData.append('title', offerForm.title.value.trim());
        formData.append('message', offerForm.message.value.trim());
        formData.append('body', offerForm.body.value.trim() || '');
        formData.append('cta_label', offerForm.cta_label.value.trim() || '');
        if (offerImageInput?.files?.[0]) formData.append('image', offerImageInput.files[0]);
        sendOffer(formData, offerForm.target.value);
    });
}
statusForm.addEventListener('submit', updateOrderStatus);
paymentForm.addEventListener('submit', updatePaymentStatus);
applyFiltersBtn.addEventListener('click', fetchOrders);
clearFiltersBtn.addEventListener('click', () => {
    filterStatus.value = '';
    filterPaymentMethod.value = '';
    filterPaymentStatus.value = '';
    filterDateFrom.value = '';
    filterDateTo.value = '';
    fetchOrders();
});
exportCsvBtn.addEventListener('click', exportCsv);
proofModalCloseBtn.addEventListener('click', closeProofModal);
proofModal.addEventListener('click', (event) => {
    if (event.target === proofModal) closeProofModal();
});
adminLogoutBtn.addEventListener('click', () => {
    clearAuth();
    window.location.href = '/admin/login';
});
adminMenuTabs.forEach(tab => {
    tab.addEventListener('click', () => showAdminTab(tab.dataset.target));
});
['click', 'keydown', 'mousemove', 'touchstart', 'scroll'].forEach(evt => {
    window.addEventListener(evt, touchAdminSession, { passive: true });
});

boot();
syncProductAvailabilityLabel();
bindImagePreview(productImageInput, productImagePreview);
bindImagePreview(offerImageInput, offerImagePreview);
if (removeProductImageBtn) {
    removeProductImageBtn.addEventListener('click', () => {
        productImageRemoved = true;
        if (productImageInput) productImageInput.value = '';
        if (productImagePreview) productImagePreview.dataset.persisted = '';
        setUploadPreview(productImagePreview, '');
    });
}
</script>
</body>
</html>
<?php /**PATH C:\Proyectos\WEB_POLLERIA\laravel-app\resources\views/admin/panel.blade.php ENDPATH**/ ?>