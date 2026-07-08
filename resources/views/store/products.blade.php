@extends('store.layout')

@section('title', 'Pollos y Parrillas "El Dorado"')

@section('content')
    <section class="catalog-shell">
        <section class="hero-showcase surface">
            <div class="hero-hours-bar">Horario de atencion · Lunes a Viernes 12:00 pm a 8:00 pm · Sabado 11:00 am a 9:00 pm · Domingo 11:00 am a 7:00 pm</div>
            <div class="catalog-hero">
                <div class="hero-copy-stack">
                <div class="hero-logo-lockup">
                    <div class="hero-logo-badge"><img src="/images/ico-pollo.jpg" alt="El Dorado"></div>
                    <div class="hero-logo-copy"><span>Pollos y Parrillas</span><strong>El Dorado</strong></div>
                </div>
                <p class="eyebrow">Menu</p>
                <h2 class="title hero-slogan">SA<br>BRO<br>SO!</h2>
                <p class="muted-main hero-text">
                    Pollo, parrilla y bebidas en una vitrina mas directa, mas viva y mas facil de comprar.
                </p>
                <a href="#productsGrid" class="hero-cta">Ver menu</a>
                </div>

            <div id="heroSlider" class="hero-visual-stage">
                <div class="hero-stage-left">
                    <div class="hero-stage-rays"></div>
                <article class="hero-feature hero-feature-main">
                    <img id="heroImageA" src="/images/hero/slide-1.jpg" alt="Promo El Dorado 1" class="hero-poster">
                    <div class="hero-tint hero-tint-soft"></div>
                </article>
                    <div class="hero-plate-copy">
                        <strong>Brasa protagonista</strong>
                        <span>Textura crocante, porcion potente y compra rapida.</span>
                    </div>
                </div>
                <div class="hero-stage-right">
                <article class="hero-feature hero-feature-side">
                    <img id="heroImageB" src="/images/hero/slide-2.jpg" alt="Promo El Dorado 2" class="hero-poster">
                    <div class="hero-tint hero-tint-soft"></div>
                    <div class="hero-note">
                        <strong>Combos</strong>
                        <span>Listos para compartir.</span>
                    </div>
                </article>
                <article class="hero-feature hero-feature-side">
                    <img id="heroImageC" src="/images/hero/slide-3.jpg" alt="Promo El Dorado 3" class="hero-poster">
                    <div class="hero-tint"></div>
                    <div class="hero-note">
                        <strong>Bebidas</strong>
                        <span>El cierre exacto para acompañar cualquier pedido.</span>
                    </div>
                </article>
                    <div class="hero-quality-chip">100% sabor dorado</div>
                </div>
            </div>
            </div>
        </section>

        <section class="catalog-tools surface">
            <div class="tools-head">
                <div>
                    <p class="eyebrow">Busqueda guiada</p>
                    <h3 class="section-title">Filtra por antojo, categoria o presupuesto.</h3>
                </div>
                <div id="filterInfo" class="tools-info">Escribe o selecciona una categoria para empezar.</div>
            </div>

            <div class="tool-grid">
                <div class="tool-card">
                    <label for="searchInput" class="label-main">Buscar por nombre</label>
                    <input id="searchInput" type="text" class="input-main" placeholder="Ej: pollo, parrilla, chicha...">
                </div>
                <div class="tool-card">
                    <label for="categoryInput" class="label-main">Categoria</label>
                    <select id="categoryInput" class="select-main">
                        <option value="">Todas</option>
                        <option value="pollos">Pollos</option>
                        <option value="parrillas">Parrillas</option>
                        <option value="bebidas">Bebidas</option>
                    </select>
                </div>
                <div class="tool-card">
                    <label for="maxPriceInput" class="label-main">Precio maximo</label>
                    <input id="maxPriceInput" type="number" step="0.10" min="0" class="input-main" placeholder="Ej: 40.00">
                </div>
            </div>

            <div class="quick-filter-row">
                <button type="button" class="btn-soft" data-quick-category="">Ver todo</button>
                <button type="button" class="btn-soft" data-quick-category="pollos">Solo pollos</button>
                <button type="button" class="btn-soft" data-quick-category="parrillas">Solo parrillas</button>
                <button type="button" class="btn-soft" data-quick-category="bebidas">Solo bebidas</button>
                <button type="button" class="btn-soft" data-quick-budget="25">Hasta S/ 25</button>
                <button type="button" class="btn-soft" data-quick-budget="40">Hasta S/ 40</button>
            </div>

            <div id="searchState" class="search-state-panel" style="display:none;">
                <img src="/images/ui/processing-chicken.png" alt="Procesando" class="search-state-art">
                <div>
                    <strong id="searchStateTitle">Espera, estamos buscando...</strong>
                    <div id="searchStateText" class="muted-main">Filtrando productos para mostrarte el mejor resultado.</div>
                </div>
            </div>
        </section>

        <section class="catalog-board">
            <div class="catalog-column catalog-column-grid">
                <div id="productsGrid" class="products-grid"></div>
            </div>
        </section>
    </section>

    <div class="float-cart" id="floatCart">
        <button type="button" id="floatCartToggle" class="float-cart-toggle" aria-expanded="false">
            <span class="float-cart-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M7 5h14l-1.5 8.5H9L7 5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M7 5 6.2 3H3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <circle cx="10" cy="19" r="1.6" stroke="currentColor" stroke-width="1.6"/>
                    <circle cx="18" cy="19" r="1.6" stroke="currentColor" stroke-width="1.6"/>
                </svg>
            </span>
            <span class="float-cart-label">Carrito</span>
            <span id="floatCartCount" class="float-cart-count">0</span>
        </button>

        <div id="floatCartPanel" class="float-cart-panel" aria-hidden="true">
            <div class="float-cart-head">
                <div>
                    <p class="eyebrow" style="margin:0 0 6px;">Tu pedido</p>
                    <strong class="float-cart-title">Resumen</strong>
                </div>
                <button type="button" id="floatCartClose" class="btn-soft" style="padding:10px 12px;">Cerrar</button>
            </div>
            <div id="floatCartBody" class="float-cart-body"></div>
            <div class="float-cart-actions">
                <a class="btn-main" href="{{ route('store.cart') }}" style="text-decoration:none; justify-content:center;">Ir al pago</a>
                <a class="btn-soft" href="{{ route('store.cart') }}" style="text-decoration:none; justify-content:center;">Ver carrito</a>
            </div>
        </div>
    </div>

    <div id="toast" class="toast" role="status" aria-live="polite"></div>

    <div id="productModal" class="product-modal">
        <div class="product-modal-card">
            <div class="product-modal-media">
                <img id="modalImage" alt="" class="product-modal-image">
            </div>
            <div class="product-modal-body">
                <p class="eyebrow">Detalle del Producto</p>
                <h3 id="modalName" class="section-title"></h3>
                <p id="modalDesc" class="muted-main"></p>
                <p class="product-modal-price"><strong id="modalPrice"></strong></p>
                <button id="closeModalBtn" class="btn-soft">Cerrar</button>
            </div>
        </div>
    </div>

    <style>
        .catalog-shell {
            display: grid;
            gap: 18px;
        }

        .hero-showcase {
            padding: 0;
            overflow: hidden;
            background:
                linear-gradient(180deg, #fff8e4 0%, #fff0c4 36%, #f6c810 100%);
        }

        .hero-hours-bar {
            padding: 8px 20px;
            background: rgba(255, 187, 0, .95);
            color: #1E1308;
            text-align: center;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .03em;
        }

        .hero-logo-lockup {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #fff7ed;
        }

        .hero-logo-badge {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,.16);
            background: rgba(255,255,255,.1);
        }

        .hero-logo-badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-logo-copy {
            display: grid;
            gap: 2px;
        }

        .hero-logo-copy span {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(255,255,255,.72);
        }

        .hero-logo-copy strong {
            font-size: 20px;
            line-height: 1;
        }

        .hero-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .hero-badges span,
        .hero-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.34);
            color: #fff8ef;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            text-decoration: none;
            box-shadow: 0 10px 22px rgba(0,0,0,.16);
        }

        .hero-cta {
            background: linear-gradient(135deg, #ffad18, #f28d00);
            border-color: rgba(255,255,255,.55);
        }

        .catalog-hero {
            display: grid;
            grid-template-columns: .7fr 1.3fr;
            gap: 20px;
            align-items: stretch;
            padding: 18px 24px 24px;
        }

        .hero-copy-stack {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 14px;
            min-width: 0;
        }

        .catalog-hero .eyebrow {
            color: rgba(255,255,255,.72);
        }

        .catalog-hero .title {
            margin: 0;
            max-width: 280px;
            font-size: clamp(62px, 7vw, 110px);
            line-height: .82;
            color: #fffdf8;
            text-shadow: 0 8px 18px rgba(0, 0, 0, .3);
            letter-spacing: -.05em;
        }

        .hero-text {
            max-width: 320px;
            font-size: 14px;
            color: rgba(255,255,255,.82);
        }

        .hero-badges span {
            background: rgba(255,255,255,.08);
        }

        .hero-badges-soft span {
            background: rgba(255,255,255,.92);
            color: #8d4a1a;
            border-color: rgba(255,255,255,.92);
        }

        .hero-visual-stage {
            display: grid;
            grid-template-columns: 1.08fr .92fr;
            gap: 16px;
            min-height: 520px;
        }

        .hero-stage-left {
            position: relative;
            display: grid;
            align-items: end;
            padding: 18px;
            border-radius: 34px;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, .72), transparent 24%),
                linear-gradient(120deg, #fff8e9 0%, #fdf2c7 42%, #f7c319 100%);
        }

        .hero-stage-rays {
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(125deg, rgba(255,255,255,.16) 0 10px, transparent 10px 28px),
                radial-gradient(circle at 20% 80%, rgba(255,255,255,.18), transparent 30%);
            mix-blend-mode: screen;
            opacity: .8;
            pointer-events: none;
        }

        .hero-stage-right {
            display: grid;
            grid-template-rows: 1fr 1fr auto;
            gap: 14px;
            padding: 20px 10px 10px 0;
        }

        .hero-feature {
            position: relative;
            overflow: hidden;
            min-height: 220px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,.24);
            box-shadow: 0 22px 42px rgba(26, 10, 0, .18);
            background: rgba(255,255,255,.08);
        }

        .hero-feature-main {
            min-height: 440px;
            background: radial-gradient(circle at top, rgba(255,255,255,.16), transparent 46%);
        }

        .hero-feature-side {
            min-height: 0;
        }

        .hero-poster {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .45s ease, filter .45s ease;
        }

        .hero-feature:hover .hero-poster {
            transform: scale(1.06);
            filter: saturate(1.08);
        }

        .hero-tint {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(34, 16, 4, .34), rgba(34, 16, 4, .08));
        }

        .hero-tint-soft {
            background: linear-gradient(to top, rgba(34, 16, 4, .72), rgba(34, 16, 4, .12));
        }

        .hero-note,
        .hero-plate-copy {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 18px;
            display: grid;
            gap: 4px;
            color: #fff4eb;
            z-index: 2;
        }

        .hero-note strong,
        .hero-plate-copy strong {
            font-size: 22px;
        }

        .hero-note span,
        .hero-plate-copy span {
            font-size: 13px;
            line-height: 1.45;
        }

        .hero-quality-chip {
            justify-self: end;
            padding: 11px 16px;
            border-radius: 999px;
            background: rgba(255,255,255,.92);
            color: #954e1a;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            box-shadow: 0 12px 24px rgba(69, 23, 0, .14);
        }

        .catalog-tools {
            padding: 18px;
            display: grid;
            gap: 14px;
        }

        .tools-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 14px;
            flex-wrap: wrap;
        }

        .tools-head .section-title {
            font-size: clamp(22px, 3vw, 30px);
            line-height: 1.04;
        }

        .tools-info {
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(234, 182, 138, .86);
            background: rgba(255, 247, 240, .86);
            color: #82471f;
            font-size: 13px;
            font-weight: 800;
        }

        .tool-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .tool-card {
            padding: 16px;
            border-radius: 20px;
            border: 1px solid rgba(234, 182, 138, .76);
            background: linear-gradient(180deg, #fffdfa 0%, #fff5ed 100%);
        }

        .quick-filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .search-state-panel {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 22px;
            border: 1px solid rgba(234, 182, 138, .78);
            background: linear-gradient(180deg, #fff8f1 0%, #ffefe0 100%);
        }

        .search-state-art {
            width: 76px;
            height: 76px;
            object-fit: contain;
            border-radius: 18px;
            border: 1px solid rgba(234, 182, 138, .8);
            background: #fff;
            padding: 6px;
        }

        .catalog-board {
            display: grid;
            gap: 18px;
        }

        .catalog-column {
            display: grid;
            gap: 18px;
        }

        .float-cart {
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 9998;
            display: grid;
            gap: 10px;
            width: min(380px, calc(100vw - 32px));
            pointer-events: none;
            opacity: 0;
            transform: translateY(12px);
            transition: opacity .18s ease, transform .18s ease;
        }

        .float-cart.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .float-cart-toggle,
        .float-cart-panel {
            pointer-events: auto;
        }

        .float-cart-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
            border: 1px solid rgba(234, 182, 138, .9);
            border-radius: 999px;
            padding: 12px 14px;
            background: linear-gradient(120deg, rgba(255, 111, 31, .92), rgba(255, 157, 90, .92));
            color: var(--accent-ink);
            font-weight: 900;
            box-shadow: 0 18px 44px rgba(52, 17, 0, .18);
            cursor: pointer;
        }

        .float-cart-icon {
            width: 36px;
            height: 36px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 251, 247, .78);
            border: 1px solid rgba(234, 182, 138, .7);
        }

        .float-cart-label {
            margin-right: auto;
        }

        .float-cart-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            border: 1px solid rgba(45, 20, 6, .18);
            background: rgba(255, 251, 247, .82);
        }

        .float-cart-panel {
            display: none;
            border-radius: 26px;
            border: 1px solid rgba(234, 182, 138, .85);
            background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,246,238,.98));
            box-shadow: 0 24px 70px rgba(52, 17, 0, .18);
            overflow: hidden;
        }

        .float-cart-panel.open {
            display: block;
        }

        .float-cart-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 14px 10px;
            border-bottom: 1px solid rgba(234, 182, 138, .55);
            background: rgba(255, 247, 240, .65);
        }

        .float-cart-title {
            display: block;
            font-size: 18px;
        }

        .float-cart-body {
            display: grid;
            gap: 10px;
            padding: 12px 14px 0;
            max-height: min(52vh, 420px);
            overflow: auto;
        }

        .float-cart-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 12px;
            border-radius: 18px;
            border: 1px solid rgba(234, 182, 138, .55);
            background: rgba(255, 247, 240, .55);
        }

        .float-cart-name {
            font-weight: 900;
            color: var(--panel-ink);
            line-height: 1.15;
        }

        .float-cart-meta {
            color: var(--muted-ink);
            font-size: 12px;
            margin-top: 4px;
        }

        .float-cart-price {
            white-space: nowrap;
            font-weight: 900;
            color: #b44c00;
        }

        .float-cart-total {
            display: flex;
            justify-content: space-between;
            padding: 12px 14px;
            margin-top: 8px;
            border-top: 1px solid rgba(234, 182, 138, .55);
            font-weight: 900;
        }

        .float-cart-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 0 14px 14px;
        }

        .toast {
            position: fixed;
            right: 16px;
            bottom: 86px;
            z-index: 9999;
            max-width: min(420px, calc(100vw - 32px));
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid rgba(234, 182, 138, .85);
            background: rgba(255, 247, 240, .96);
            color: var(--accent-ink);
            box-shadow: 0 10px 28px rgba(25, 12, 6, .18);
            transform: translateY(10px);
            opacity: 0;
            pointer-events: none;
            transition: opacity .18s ease, transform .18s ease;
            font-weight: 800;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .product-card {
            display: grid;
            gap: 12px;
            border-radius: 28px;
            padding: 18px;
            background: #fffdf5;
            border: 1px solid rgba(255, 187, 0, .18);
            box-shadow: 0 22px 46px rgba(92, 47, 12, .08);
        }

        .product-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
        }

        .product-name {
            margin: 0;
            font-size: 22px;
            color: #1E1308;
            line-height: 1.04;
        }

        .product-category {
            display: inline-flex;
            margin-top: 8px;
            padding: 7px 10px;
            border-radius: 999px;
            border: 1px solid rgba(234, 182, 138, .78);
            background: rgba(255, 247, 240, .9);
            color: #8a4a1f;
            font-size: 12px;
            font-weight: 900;
            text-transform: capitalize;
        }

        .product-price {
            margin: 0;
            color: #b44c00;
            font-size: 30px;
            font-weight: 900;
            white-space: nowrap;
        }

        .product-description {
            min-height: 44px;
            font-size: 14px;
        }

        .product-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
            border: 1px solid rgba(234, 182, 138, .78);
            background: rgba(255, 247, 240, .88);
            color: #7e451d;
        }

        .status-chip::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(120deg, #2dbf72, #77da9f);
        }

        .status-chip.sold-out {
            color: #9a3610;
            background: #fff1ea;
            border-color: #ffc4af;
        }

        .status-chip.sold-out::before {
            background: linear-gradient(120deg, #e76b3c, #ffb398);
        }

        .stock-alert {
            margin: 0;
            padding: 10px 12px;
            border-radius: 16px;
            border: 1px dashed #ffbe92;
            background: #fff5ee;
            color: #8f4207;
            font-size: 13px;
            font-weight: 700;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .product-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: clamp(14px, 3vw, 28px);
            background: rgba(28, 15, 8, .62);
            backdrop-filter: blur(8px);
            z-index: 9999;
        }

        .product-modal-card {
            display: grid;
            grid-template-columns: 1fr;
            width: min(520px, 100%);
            max-height: min(78vh, 680px);
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid rgba(234, 182, 138, .8);
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ed 100%);
            box-shadow: 0 28px 60px rgba(52, 17, 0, .18);
            animation: modalPop .18s ease-out;
        }

        .product-modal-media {
            min-height: 200px;
            max-height: 260px;
            background: linear-gradient(140deg, #ffe5ce, #fff6ef);
        }

        .product-modal-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-modal-body {
            padding: clamp(20px, 3vw, 28px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 14px;
            overflow: auto;
        }

        @keyframes modalPop {
            from { transform: translateY(10px) scale(.98); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .product-modal-price {
            margin: 0;
            font-size: 26px;
            color: #b44c00;
        }

        @media (max-width: 1040px) {
            .catalog-hero {
                grid-template-columns: 1fr;
            }

            .hero-visual-stage {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .product-modal-card {
                width: min(520px, 100%);
            }
        }

        @media (max-width: 760px) {
            .tool-grid,
            .product-modal-card {
                grid-template-columns: 1fr;
            }

            .catalog-hero .title {
                font-size: 72px;
                max-width: none;
            }

            .hero-stage-left,
            .hero-stage-right {
                padding: 14px;
            }

            .hero-feature-main {
                min-height: 300px;
            }

            .hero-feature-side {
                min-height: 180px;
            }

            .product-modal-card {
                max-height: none;
                width: min(560px, 100%);
            }

            .product-modal-media {
                min-height: 220px;
                max-height: 260px;
            }
        }

        .hero-showcase,
        .catalog-tools,
        .tool-card,
        .product-card,
        .float-cart-panel,
        .product-modal-card {
            border-color: rgba(255,122,26,.22) !important;
        }
        .tools-info,
        .product-category,
        .status-chip,
        .stock-alert {
            background: rgba(255,247,240,.95) !important;
            color: var(--ink) !important;
            border-color: rgba(255,122,26,.22) !important;
        }
        .product-name,
        .section-title,
        .float-cart-name,
        .float-cart-title {
            color: var(--ink) !important;
        }
        .product-price,
        .product-modal-price,
        .eyebrow {
            color: var(--orange-deep) !important;
        }
        .muted-main,
        .float-cart-meta {
            color: var(--muted-ink) !important;
        }
        .product-image-wrap,
        .hero-feature,
        .search-state-panel,
        .search-state-art,
        .float-cart-row {
            background: #fffaf4 !important;
            border-color: rgba(255,122,26,.18) !important;
        }

        .hero-showcase {
            border-radius: 8px;
            background:
                linear-gradient(120deg, rgba(255, 62, 52, .28), transparent 22%),
                linear-gradient(135deg, #101512 0%, #18352b 35%, #5b1f18 58%, #ffc20e 58%, #ffc20e 100%);
            box-shadow: 0 18px 38px rgba(62, 24, 0, .12);
        }

        .hero-hours-bar {
            background: #ffc20e;
            color: #fff;
            text-shadow: 0 1px 2px rgba(50, 20, 0, .22);
        }

        .catalog-hero {
            grid-template-columns: minmax(230px, .72fr) minmax(0, 1.28fr);
            align-items: center;
            min-height: 520px;
        }

        .hero-logo-lockup {
            margin-bottom: 8px;
        }

        .hero-copy-stack {
            padding: 10px 0;
        }

        .hero-copy-stack .eyebrow {
            color: #ffae3d !important;
        }

        .catalog-hero .title {
            font-size: clamp(64px, 7vw, 112px);
            line-height: .82;
            letter-spacing: 0;
        }

        .hero-cta {
            width: fit-content;
            background: #ffad18;
        }

        .hero-visual-stage {
            min-height: 440px;
        }

        .hero-stage-left,
        .hero-feature {
            border-radius: 8px;
        }

        .hero-stage-right {
            padding-top: 0;
        }

        .hero-stage-right .hero-feature {
            min-height: 190px;
        }

        .hero-quality-chip {
            border-radius: 8px;
        }

        .catalog-tools {
            border-radius: 8px;
        }

        @media (max-width: 1040px) {
            .catalog-hero {
                grid-template-columns: 1fr;
                min-height: auto;
            }
        }

        @media (max-width: 760px) {
            .catalog-hero {
                padding: 16px;
            }

            .catalog-hero .title {
                font-size: 64px;
            }

            .hero-visual-stage {
                grid-template-columns: 1fr;
            }
        }

        /* Banner final alineado al tema de referencia. */
        .catalog-shell {
            gap: 14px;
        }

        .hero-showcase {
            border-radius: 0;
            border: 1px solid rgba(255, 194, 14, .28);
            background:
                linear-gradient(90deg, #17110d 0%, #17110d 56%, #ffc20e 56%, #ffc20e 100%) !important;
            box-shadow: none;
        }

        .hero-hours-bar {
            padding: 9px 16px;
            background: #ffc20e;
            color: #fff;
            font-size: 12px;
            font-weight: 900;
        }

        .catalog-hero {
            grid-template-columns: minmax(230px, .62fr) minmax(0, 1.38fr);
            min-height: 360px;
            gap: 18px;
            padding: 18px 22px 22px;
        }

        .hero-logo-lockup {
            margin: 0 0 12px;
        }

        .hero-logo-badge {
            width: 48px;
            height: 48px;
            border-radius: 8px;
        }

        .hero-logo-copy span {
            color: rgba(255, 255, 255, .62);
        }

        .hero-logo-copy strong {
            color: #fff8ed;
        }

        .hero-copy-stack {
            gap: 10px;
            padding: 0;
        }

        .hero-copy-stack .eyebrow {
            color: #ffc20e !important;
            margin: 0;
        }

        .catalog-hero .title {
            max-width: 230px;
            font-size: clamp(56px, 6vw, 86px);
            line-height: .82;
            color: #fff;
        }

        .hero-text {
            max-width: 300px;
            color: rgba(255, 248, 237, .74);
        }

        .hero-badges {
            gap: 8px;
        }

        .hero-badges span,
        .hero-cta {
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 10px;
        }

        .hero-badges-soft span {
            background: #fff8ed;
            color: #7a3b0d;
        }

        .hero-cta {
            background: #ffc20e;
            color: #271204;
            box-shadow: none;
        }

        /*
         * Correcciones visuales solicitadas: el hero debe quedar limpio,
         * sin chips que parezcan botones sin accion, y el carrito visible.
         */
        .hero-badges,
        .hero-badges-soft {
            display: none !important;
        }

        .hero-cta {
            width: fit-content;
            min-height: 44px;
            padding: 12px 18px !important;
            border: 0 !important;
            border-radius: 999px !important;
            background: linear-gradient(135deg, #ffcf3a 0%, #f2b705 52%, #d97904 100%) !important;
            color: #1b1008 !important;
            box-shadow: 0 14px 30px rgba(217, 121, 4, .28) !important;
        }

        .float-cart {
            opacity: 1;
            transform: translateY(0);
        }

        .float-cart-toggle {
            min-height: 58px;
            border: 1px solid rgba(255, 207, 58, .66) !important;
            background: linear-gradient(135deg, #ffcf3a 0%, #f2b705 52%, #d97904 100%) !important;
            color: #1b1008 !important;
            box-shadow: 0 18px 42px rgba(0, 0, 0, .30) !important;
        }

        .float-cart-toggle *,
        .float-cart-label,
        .float-cart-count {
            color: #1b1008 !important;
        }

        .float-cart-icon,
        .float-cart-count {
            background: rgba(255, 248, 236, .72) !important;
            border-color: rgba(27, 16, 8, .16) !important;
        }

        .hero-visual-stage {
            grid-template-columns: 1fr 230px;
            gap: 14px;
            min-height: 320px;
        }

        .hero-stage-left {
            min-height: 320px;
            border-radius: 8px;
            padding: 14px;
            background:
                repeating-linear-gradient(125deg, rgba(255,255,255,.13) 0 10px, transparent 10px 26px),
                linear-gradient(135deg, #063929 0%, #143a2f 46%, #72221a 100%);
        }

        .hero-stage-rays {
            opacity: .28;
        }

        .hero-feature {
            border-radius: 8px;
            border-color: rgba(255, 255, 255, .32);
            background: #fffaf2 !important;
        }

        .hero-feature-main {
            min-height: 270px;
            max-width: 360px;
            justify-self: center;
            align-self: end;
        }

        .hero-stage-right {
            grid-template-rows: 1fr 1fr auto;
            gap: 12px;
            padding: 0;
        }

        .hero-stage-right .hero-feature {
            min-height: 145px;
        }

        .hero-note strong,
        .hero-plate-copy strong {
            font-size: 17px;
        }

        .hero-note span,
        .hero-plate-copy span {
            font-size: 11px;
        }

        .hero-quality-chip {
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 10px;
        }

        .catalog-tools {
            border-radius: 0;
            background: #fffaf2 !important;
        }

        @media (max-width: 980px) {
            .hero-showcase {
                background: linear-gradient(180deg, #17110d 0%, #17110d 54%, #ffc20e 54%, #ffc20e 100%) !important;
            }

            .catalog-hero,
            .hero-visual-stage {
                grid-template-columns: 1fr;
            }

            .hero-feature-main {
                max-width: none;
            }
        }

        /* Villa-style hero requested from the reference image. */
        .hero-showcase {
            position: relative;
            border-radius: 0 !important;
            border: 0 !important;
            background:
                linear-gradient(90deg, rgba(0, 0, 0, .96) 0%, rgba(0, 0, 0, .82) 36%, rgba(30, 17, 9, .50) 58%, rgba(127, 70, 18, .10) 100%),
                linear-gradient(180deg, #050505 0%, #21140a 50%, #8b581d 100%) !important;
            box-shadow: none !important;
        }

        .hero-showcase::after {
            content: "El Dorado";
            position: absolute;
            right: 16px;
            bottom: 16px;
            z-index: 5;
            min-height: 76px;
            padding: 16px 24px 16px 92px;
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(255, 207, 58, .42);
            border-radius: 18px;
            background:
                linear-gradient(135deg, rgba(16, 13, 10, .90), rgba(58, 33, 19, .82)),
                url('/images/ico-pollo.jpg') 18px center / 56px 56px no-repeat;
            color: #fff8ec;
            font-size: clamp(22px, 3vw, 34px);
            font-weight: 900;
            letter-spacing: .01em;
            box-shadow: 0 18px 36px rgba(0, 0, 0, .30);
        }

        .hero-hours-bar {
            background: #000 !important;
            color: #f4f0e8 !important;
            text-transform: uppercase;
        }

        .catalog-hero {
            min-height: 460px;
            padding: 70px 32px 64px !important;
            background:
                linear-gradient(90deg, rgba(0,0,0,.76), rgba(0,0,0,.26) 48%, rgba(0,0,0,.04)),
                url('/images/hero/slide-1.jpg') center right / cover no-repeat;
        }

        .hero-copy-stack {
            max-width: 430px;
            padding-left: clamp(10px, 4vw, 58px) !important;
        }

        .hero-logo-lockup {
            color: #fff !important;
        }

        .hero-logo-badge {
            display: none;
        }

        .hero-logo-copy span {
            color: rgba(255, 255, 255, .68) !important;
        }

        .hero-logo-copy strong {
            color: #fff !important;
            font-style: italic;
            font-size: 30px !important;
            text-shadow: 0 3px 14px rgba(255,255,255,.22);
        }

        .hero-copy-stack .eyebrow {
            color: #ffbf00 !important;
            font-size: 34px !important;
            line-height: 1 !important;
            letter-spacing: -.04em !important;
            text-transform: none !important;
            font-style: italic;
        }

        .catalog-hero .title {
            max-width: 420px !important;
            color: #fff !important;
            font-size: clamp(68px, 9vw, 132px) !important;
            line-height: .76 !important;
            letter-spacing: -.07em !important;
            text-transform: uppercase;
            text-shadow: 0 10px 24px rgba(0, 0, 0, .65) !important;
        }

        .hero-text {
            color: rgba(255, 255, 255, .82) !important;
            max-width: 360px !important;
        }

        .hero-badges span,
        .hero-cta {
            background: rgba(0, 0, 0, .66) !important;
            color: #fff !important;
            border-color: rgba(255, 255, 255, .24) !important;
        }

        .hero-cta {
            background: #ffbf00 !important;
            color: #250f02 !important;
            border-color: #ffbf00 !important;
        }

        .hero-visual-stage {
            display: none !important;
        }

        @media (max-width: 980px) {
            .catalog-hero {
                min-height: 520px;
                background:
                    linear-gradient(180deg, rgba(0,0,0,.88), rgba(0,0,0,.50) 50%, rgba(0,0,0,.08)),
                    url('/images/hero/slide-1.jpg') center bottom / cover no-repeat;
            }

            .hero-showcase::after {
                left: 16px;
                right: 16px;
                text-align: center;
                justify-content: center;
            }
        }

        /* El Dorado final product-card lock: readable warm cards. */
        .products-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
            align-items: stretch;
        }

        .products-grid .product-card {
            display: grid;
            grid-template-rows: auto 1fr auto;
            gap: 14px;
            min-height: 430px;
            border: 1px solid rgba(234, 182, 138, .78) !important;
            border-radius: 26px !important;
            padding: 16px !important;
            background: linear-gradient(180deg, #fffdfb 0%, #fff5ec 100%) !important;
            color: #25170f !important;
            box-shadow: 0 18px 34px rgba(52, 17, 0, .07) !important;
        }

        .products-grid .product-card,
        .products-grid .product-card * {
            text-shadow: none !important;
        }

        .products-grid .product-head {
            align-items: flex-start;
            color: #25170f !important;
        }

        .products-grid .product-name {
            display: -webkit-box;
            min-height: 54px;
            max-width: 150px;
            overflow: hidden;
            color: #25170f !important;
            font-size: 21px !important;
            line-height: 1.06 !important;
            font-weight: 950 !important;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .products-grid .product-price {
            flex-shrink: 0;
            color: #f25d00 !important;
            font-size: 30px !important;
            line-height: 1 !important;
            font-weight: 950 !important;
            white-space: nowrap;
        }

        .products-grid .product-category,
        .products-grid .status-chip {
            color: #82471f !important;
            border-color: rgba(234, 182, 138, .82) !important;
            background: rgba(255, 247, 240, .92) !important;
        }

        .products-grid .status-chip {
            font-size: 11px !important;
            font-weight: 900 !important;
        }

        .products-grid .product-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .products-grid .product-actions .btn-main,
        .products-grid .product-actions .btn-soft {
            min-height: 44px;
            padding: 10px 12px !important;
            color: #2d1406 !important;
            white-space: nowrap;
        }

        .products-grid .product-image-wrap {
            aspect-ratio: 4 / 3 !important;
            border-radius: 22px !important;
            background: linear-gradient(145deg, #ffe9d7, #fffaf5) !important;
        }

        .products-grid .product-image {
            background: #fff8f2;
        }

        @media (max-width: 560px) {
            .products-grid .product-head {
                display: grid;
                grid-template-columns: 1fr;
            }

            .products-grid .product-name {
                max-width: none;
            }
        }
    </style>
@endsection

@section('scripts')
<script>
const HERO_FALLBACKS = [
    ['/images/hero/slide-1.jpg', '/images/hero/slide-2.jpg'],
    ['/images/hero/slide-2.jpg', '/images/hero/slide-1.jpg'],
    ['/images/hero/slide-3.jpg', '/images/hero/slide-2.jpg'],
];

const heroImages = [
    document.getElementById('heroImageA'),
    document.getElementById('heroImageB'),
    document.getElementById('heroImageC'),
];
const productsGrid = document.getElementById('productsGrid');
const searchInput = document.getElementById('searchInput');
const categoryInput = document.getElementById('categoryInput');
const maxPriceInput = document.getElementById('maxPriceInput');
const filterInfo = document.getElementById('filterInfo');
const modal = document.getElementById('productModal');
const searchState = document.getElementById('searchState');
const searchStateTitle = document.getElementById('searchStateTitle');
const searchStateText = document.getElementById('searchStateText');
const floatCartToggle = document.getElementById('floatCartToggle');
const floatCartClose = document.getElementById('floatCartClose');
const floatCartPanel = document.getElementById('floatCartPanel');
const floatCartCountEl = document.getElementById('floatCartCount');
const floatCartBodyEl = document.getElementById('floatCartBody');
const floatCartEl = document.getElementById('floatCart');
const toastEl = document.getElementById('toast');
const heroProductsMetric = document.getElementById('heroProductsMetric');
const heroAvailableMetric = document.getElementById('heroAvailableMetric');
const quickCategoryButtons = Array.from(document.querySelectorAll('[data-quick-category]'));
const quickBudgetButtons = Array.from(document.querySelectorAll('[data-quick-budget]'));

if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);

const state = { products: [] };
let slideIndex = 0;
let searchTimer = null;
let heroPools = HERO_FALLBACKS.map(group => [...group]);

function getToken() { return localStorage.getItem('ed_token'); }
function isLoggedIn() { return Boolean(getToken()); }
function getCart() { return JSON.parse(localStorage.getItem('ed_cart') || '[]'); }
function setCart(cart) {
    localStorage.setItem('ed_cart', JSON.stringify(cart));
    window.dispatchEvent(new Event('storage'));
}
function money(n) { return Number(n).toFixed(2); }

let toastTimer = null;
function showToast(message) {
    if (!toastEl) return;
    toastEl.innerHTML = message;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toastEl.classList.remove('show'), 2200);
}

function escapeHtml(raw) {
    return String(raw || '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function setCartOpen(open) {
    if (!floatCartPanel || !floatCartToggle) return;
    floatCartPanel.classList.toggle('open', open);
    floatCartPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
    floatCartToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
}

function setFloatCartVisible(visible) {
    if (!floatCartEl) return;
    floatCartEl.classList.toggle('visible', Boolean(visible));
}

function renderFloatCart() {
    if (!floatCartBodyEl || !floatCartCountEl) return;

    const cart = getCart();
    const count = cart.reduce((sum, item) => sum + Number(item.qty || 0), 0);
    const total = cart.reduce((sum, item) => sum + (Number(item.price || 0) * Number(item.qty || 0)), 0);

    floatCartCountEl.textContent = String(count);
    // Mostrar siempre si hay productos; si no, mostrar solo cuando el usuario ya hizo scroll.
    const scrolledEnough = (window.scrollY || 0) > 120;
    setFloatCartVisible(count > 0 || scrolledEnough);

    if (!cart.length) {
        floatCartBodyEl.innerHTML = `<div class="muted-main" style="line-height:1.5;"><strong>Aun no agregaste productos.</strong><br>Agrega un platillo y tu carrito flotante se ira actualizando.</div>`;
        return;
    }

    const rows = cart.slice(0, 6).map(item => `
        <div class="float-cart-row">
            <div>
                <div class="float-cart-name">${escapeHtml(item.name)}</div>
                <div class="float-cart-meta">x${Number(item.qty || 0)} · ${escapeHtml(item.category || 'general')}</div>
            </div>
            <div class="float-cart-price">S/ ${money(Number(item.price || 0) * Number(item.qty || 0))}</div>
        </div>
    `).join('');

    const more = cart.length > 6
        ? `<div class="muted-main" style="font-size:12px; padding:4px 2px;">+${cart.length - 6} producto(s) mas en el carrito</div>`
        : '';

    floatCartBodyEl.innerHTML = `${rows}${more}<div class="float-cart-total"><span>Total</span><span>S/ ${money(total)}</span></div>`;
}

const PURCHASE_LIMITS = {
    exact: {
        'pollo entero a la brasa': 1,
        'mega combo familiar': 1,
        '1/2 pollo a la brasa': 2,
        '1/4 pollo a la brasa': 4,
        'mostrito tradicional': 4,
        'chicha morada 1l': 2,
        'limonada frozen': 2,
    },
    sodaNames: [
        'coca-cola personal 500ml',
        'inca kola personal 500ml',
        'sprite personal 500ml',
    ],
    sodaMax: 3,
};

function setSearchState(visible, title = 'Espera, estamos buscando...', text = 'Filtrando productos para mostrarte el mejor resultado.') {
    searchState.style.display = visible ? 'flex' : 'none';
    searchStateTitle.textContent = title;
    searchStateText.textContent = text;
}

function productImage(product) {
    return product && product.image_url ? product.image_url : null;
}

function safeProductImage(product) {
    return escapeHtml(productImage(product) || '/images/products/default.svg');
}

function normalizeProductName(name) {
    return String(name || '')
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/ñ/g, 'n');
}

function validateCartLimits(cart) {
    const totals = {};
    let sodaTotal = 0;

    cart.forEach(item => {
        const normalizedName = normalizeProductName(item.name);
        const quantity = Number(item.qty || 0);
        totals[normalizedName] = (totals[normalizedName] || 0) + quantity;

        if (PURCHASE_LIMITS.sodaNames.includes(normalizedName)) {
            sodaTotal += quantity;
        }
    });

    for (const [name, max] of Object.entries(PURCHASE_LIMITS.exact)) {
        if ((totals[name] || 0) > max) {
            const label = cart.find(item => normalizeProductName(item.name) === name)?.name || name;
            return `Solo se permiten ${max} unidades de ${label} por pedido.`;
        }
    }

    if (sodaTotal > PURCHASE_LIMITS.sodaMax) {
        return `Solo se permiten ${PURCHASE_LIMITS.sodaMax} gaseosas personales por pedido.`;
    }

    return null;
}

function uniqueImages(items) {
    return [...new Set(items.filter(Boolean))];
}

function buildHeroPools() {
    const pollos = state.products.filter(product => String(product.category || '').toLowerCase() === 'pollos');
    const bebidas = state.products.filter(product => String(product.category || '').toLowerCase() === 'bebidas');

    const personal = pollos.filter(product => /1\/4|cuarto|personal|medio|1\/2|doble|para 2|dos/i.test(product.name || ''));
    const family = pollos.filter(product => /entero|familiar|combo|1 pollo|2 pollos|parrilla/i.test(product.name || ''));

    heroPools = [
        uniqueImages((personal.length ? personal : pollos).map(productImage)).length
            ? uniqueImages((personal.length ? personal : pollos).map(productImage))
            : HERO_FALLBACKS[0],
        uniqueImages((family.length ? family : [...pollos].reverse()).map(productImage)).length
            ? uniqueImages((family.length ? family : [...pollos].reverse()).map(productImage))
            : HERO_FALLBACKS[1],
        uniqueImages(bebidas.map(productImage)).length
            ? uniqueImages(bebidas.map(productImage))
            : HERO_FALLBACKS[2],
    ];

    heroImages.forEach((image, index) => {
        image.src = heroPools[index][0];
    });
}

function syncHeroMetrics() {
    if (heroProductsMetric) heroProductsMetric.textContent = `${state.products.length} productos listos`;
    if (heroAvailableMetric) {
        const available = state.products.filter(product => !product.is_sold_out && product.can_sell !== false).length;
        heroAvailableMetric.textContent = `${available} disponibles hoy`;
    }
}

function nextSlide() {
    slideIndex += 1;
    heroImages.forEach((image, index) => {
        const pool = heroPools[index] && heroPools[index].length ? heroPools[index] : HERO_FALLBACKS[index];
        image.src = pool[slideIndex % pool.length];
    });
}

function showProduct(product) {
    document.getElementById('modalImage').src = product.image_url || '/images/products/default.svg';
    document.getElementById('modalImage').alt = product.name || 'Producto';
    document.getElementById('modalName').textContent = product.name;
    document.getElementById('modalDesc').textContent = product.description || 'Sin descripcion.';
    document.getElementById('modalPrice').textContent = `Precio: S/ ${money(product.price)}`;
    modal.style.display = 'flex';
    showToast(`<div style="font-weight:900;">Elegiste: ${escapeHtml(product.name)}</div>`);
}

function addToCart(product) {
    if (!product || product.can_sell === false || product.is_sold_out) {
        alert(`Platillo agotado: ${product ? product.name : 'producto no disponible'}`);
        return;
    }
    if (!isLoggedIn()) {
        window.location.href = '/login';
        return;
    }
    const cart = getCart();
    const nextCart = cart.map(item => ({ ...item }));
    const existing = nextCart.find(item => item.id === product.id);
    if (existing) existing.qty += 1;
    else nextCart.push({
        id: product.id,
        name: product.name,
        category: product.category || '',
        price: Number(product.price),
        qty: 1,
    });
    const limitError = validateCartLimits(nextCart);
    if (limitError) {
        alert(limitError);
        return;
    }
    setCart(nextCart);
    renderFloatCart();
    setCartOpen(true);

    const safeName = escapeHtml(product.name);
    const safeImg = escapeHtml(product.image_url || '/images/products/default.svg');
    showToast(`
        <div style="display:flex; gap:10px; align-items:center;">
            <img src="${safeImg}" alt="" style="width:42px; height:42px; border-radius:14px; object-fit:cover; border:1px solid rgba(234,182,138,.7); background:#fff;">
            <div style="min-width:0;">
                <div style="font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Elegiste: ${safeName}</div>
                <div class="muted-main" style="margin-top:2px;">Agregado al carrito.</div>
            </div>
            <a href="/carrito" class="btn-soft" style="text-decoration:none; padding:10px 12px;">Ver</a>
        </div>
    `);
}

function filteredProducts() {
    const query = searchInput.value.trim().toLowerCase();
    const category = categoryInput.value.trim().toLowerCase();
    const maxPrice = maxPriceInput.value ? Number(maxPriceInput.value) : null;

    if (!query && !category && maxPrice === null) return [];

    return state.products.filter(product => {
        const byName = !query || product.name.toLowerCase().includes(query);
        const byCategory = !category || String(product.category || '').toLowerCase() === category;
        const byPrice = maxPrice === null || Number(product.price) <= maxPrice;
        return byName && byCategory && byPrice;
    });
}

function renderProducts() {
    const list = filteredProducts();

    if (!searchInput.value.trim() && !categoryInput.value && !maxPriceInput.value) {
        productsGrid.innerHTML = `
            <article class="surface panel">
                <p class="eyebrow">Explora el Menu</p>
                <h3 class="section-title">Empieza por una busqueda o una categoria.</h3>
                <p class="muted-main">El catalogo se activa cuando indicas qué te provoca hoy: pollo, parrilla o alguna bebida para completar el pedido.</p>
            </article>
        `;
        filterInfo.textContent = 'Escribe o selecciona una categoria para empezar.';
        return;
    }

    filterInfo.textContent = `${list.length} resultado(s) encontrados`;
    if (!list.length) {
        productsGrid.innerHTML = `
            <article class="surface panel">
                <p class="eyebrow">Sin coincidencias</p>
                <h3 class="section-title">No encontramos productos con ese filtro.</h3>
                <p class="muted-main">Prueba con otro nombre, cambia la categoria o amplÃ­a el precio maximo.</p>
            </article>
        `;
        return;
    }

    productsGrid.innerHTML = list.map(product => `
        <article class="product-card">
            <div class="product-image-wrap">
                <img src="${safeProductImage(product)}" alt="${escapeHtml(product.name)}" class="product-image" loading="lazy" onerror="this.onerror=null;this.src='/images/products/default.svg';">
            </div>
            <div class="product-head">
                <div>
                    <h3 class="product-name">${escapeHtml(product.name)}</h3>
                    <span class="product-category">${escapeHtml(product.category || 'general')}</span>
                </div>
                <p class="product-price">S/ ${money(product.price)}</p>
            </div>
            <div class="product-footer">
                <span class="status-chip ${product.is_sold_out ? 'sold-out' : ''}">
                    ${product.is_sold_out ? 'Platillo agotado' : 'Disponible hoy'}
                </span>
                <div class="product-actions">
                    <button type="button" data-inspect="${product.id}" class="btn-soft">Ver detalle</button>
                    <button type="button" data-buy="${product.id}" class="btn-main" ${product.is_sold_out ? 'disabled' : ''}>
                        ${product.is_sold_out ? 'Agotado' : 'Agregar'}
                    </button>
                </div>
            </div>
        </article>
    `).join('');

    productsGrid.querySelectorAll('[data-inspect]').forEach(btn => {
        const product = state.products.find(item => item.id === Number(btn.getAttribute('data-inspect')));
        btn.addEventListener('click', () => showProduct(product));
    });

    productsGrid.querySelectorAll('[data-buy]').forEach(btn => {
        const product = state.products.find(item => item.id === Number(btn.getAttribute('data-buy')));
        btn.addEventListener('click', () => addToCart(product));
    });
}

function queueRenderProducts() {
    setSearchState(true);
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        renderProducts();
        setSearchState(false);
    }, 220);
}

async function loadProducts() {
    setSearchState(true, 'Espera, estamos cargando...', 'Preparando el catalogo para que explores el menu sin perderte.');
    const res = await fetch('/api/v1/products');
    const data = await res.json();
    state.products = Array.isArray(data) ? data : [];
    buildHeroPools();
    syncHeroMetrics();
    renderProducts();
    renderFloatCart();
    setSearchState(false);
}

setInterval(nextSlide, 3500);
searchInput.addEventListener('input', queueRenderProducts);
categoryInput.addEventListener('change', queueRenderProducts);
maxPriceInput.addEventListener('input', queueRenderProducts);
quickCategoryButtons.forEach(button => {
    button.addEventListener('click', () => {
        categoryInput.value = button.getAttribute('data-quick-category') || '';
        queueRenderProducts();
    });
});
quickBudgetButtons.forEach(button => {
    button.addEventListener('click', () => {
        maxPriceInput.value = button.getAttribute('data-quick-budget') || '';
        queueRenderProducts();
    });
});
document.getElementById('closeModalBtn').addEventListener('click', () => { modal.style.display = 'none'; });
modal.addEventListener('click', (event) => { if (event.target === modal) modal.style.display = 'none'; });
window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.style.display === 'flex') modal.style.display = 'none';
});
window.addEventListener('storage', renderFloatCart);

if (floatCartToggle && floatCartPanel) {
    floatCartToggle.addEventListener('click', () => {
        const open = !floatCartPanel.classList.contains('open');
        setCartOpen(open);
    });
}
if (floatCartClose) {
    floatCartClose.addEventListener('click', () => setCartOpen(false));
}
window.addEventListener('scroll', () => renderFloatCart(), { passive: true });

// Querystring support: /productos?q=pollo
try {
    const q = new URLSearchParams(window.location.search).get('q');
    if (q && q.trim()) {
        searchInput.value = q.trim();
        queueRenderProducts();
    }
} catch {}

loadProducts();
</script>
@endsection
