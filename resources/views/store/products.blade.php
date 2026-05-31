@extends('store.layout')

@section('title', 'Pollos y Parrillas "El Dorado"')

@section('content')
    <section class="catalog-shell">
        <section class="catalog-hero surface">
            <div class="hero-copy-stack">
                <p class="eyebrow"> Menú </p>
                <h2 class="title">Compra en una ruta mas clara, mas visual y mas rapida.</h2>
                <p class="muted-main hero-text">
                    Explora los platillos con los que contamos , agregalo a tu carrito y finaliza tu pedido en pocos pasos.
                </p>
                <div class="hero-badges">
                    <span>Pollos</span>
                    <span>Parrillas</span>
                    <span>Bebidas</span>
                </div>
            </div>

            <div id="heroSlider" class="hero-visual-grid">
                <article class="hero-feature hero-feature-main">
                    <img id="heroImageA" src="/images/hero/slide-1.jpg" alt="Promo El Dorado 1" class="hero-poster">
                    <div class="hero-tint"></div>
                    <div class="hero-note">
                        <strong>Brasa protagonista</strong>
                        <span>Porciones personales con textura crocante y sabor de casa.</span>
                    </div>
                </article>
                <article class="hero-feature">
                    <img id="heroImageB" src="/images/hero/slide-2.jpg" alt="Promo El Dorado 2" class="hero-poster">
                    <div class="hero-tint"></div>
                    <div class="hero-note">
                        <strong>Combos para compartir</strong>
                        <span>Medios y enteros listos para familia o grupo.</span>
                    </div>
                </article>
                <article class="hero-feature">
                    <img id="heroImageC" src="/images/hero/slide-3.jpg" alt="Promo El Dorado 3" class="hero-poster">
                    <div class="hero-tint"></div>
                    <div class="hero-note">
                        <strong>Bebidas frias</strong>
                        <span>El cierre exacto para acompañar cualquier pedido.</span>
                    </div>
                </article>
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

        .catalog-hero {
            display: grid;
            grid-template-columns: .95fr 1.05fr;
            gap: 18px;
            padding: 20px;
        }

        .hero-copy-stack {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 18px;
        }

        .hero-text {
            max-width: 520px;
            font-size: 15px;
        }

        .hero-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .hero-badges span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(234, 182, 138, .84);
            background: rgba(255, 247, 240, .86);
            color: #82471f;
            font-size: 12px;
            font-weight: 900;
        }

        .hero-visual-grid {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            grid-template-rows: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .hero-feature {
            position: relative;
            overflow: hidden;
            min-height: 220px;
            border-radius: 26px;
            border: 1px solid rgba(234, 182, 138, .76);
            box-shadow: 0 18px 34px rgba(52, 17, 0, .08);
        }

        .hero-feature-main {
            grid-row: 1 / span 2;
        }

        .hero-poster {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .45s ease, filter .45s ease;
        }

        .hero-feature:hover .hero-poster {
            transform: scale(1.06);
            filter: saturate(1.05);
        }

        .hero-tint {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(32, 14, 5, .74), rgba(32, 14, 5, .18));
        }

        .hero-note {
            position: absolute;
            left: 16px;
            right: 16px;
            bottom: 16px;
            display: grid;
            gap: 4px;
            color: #fff4eb;
        }

        .hero-note strong {
            font-size: 20px;
        }

        .hero-note span {
            font-size: 13px;
            line-height: 1.45;
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
            color: #28160c;
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

            .product-modal-card {
                width: min(520px, 100%);
            }
        }

        @media (max-width: 760px) {
            .tool-grid,
            .hero-visual-grid,
            .product-modal-card {
                grid-template-columns: 1fr;
            }

            .hero-feature-main {
                grid-row: auto;
            }

            .hero-feature {
                min-height: 190px;
            }

            .product-modal {
                align-items: flex-start;
                overflow: auto;
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

        .catalog-hero,
        .catalog-tools,
        .tool-card,
        .product-card,
        .float-cart-panel,
        .product-modal-card {
            background: linear-gradient(180deg, #181818 0%, #101010 100%) !important;
            border-color: rgba(255,122,26,.22) !important;
            color: var(--muted-ink) !important;
        }
        .tools-info,
        .hero-badges span,
        .product-category,
        .status-chip,
        .stock-alert {
            background: rgba(25,25,25,.94) !important;
            color: var(--muted-ink) !important;
            border-color: rgba(255,122,26,.22) !important;
        }
        .product-name,
        .section-title,
        .hero-note,
        .float-cart-name,
        .float-cart-title {
            color: var(--panel-ink) !important;
        }
        .product-price,
        .product-modal-price,
        .eyebrow {
            color: #ffb15c !important;
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
            background: #0d0d0d !important;
            border-color: rgba(255,122,26,.18) !important;
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
                <img src="${product.image_url || '/images/products/default.svg'}" alt="${product.name}" class="product-image">
            </div>
            <div class="product-head">
                <div>
                    <h3 class="product-name">${product.name}</h3>
                    <span class="product-category">${product.category || 'general'}</span>
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
    renderProducts();
    renderFloatCart();
    setSearchState(false);
}

setInterval(nextSlide, 3500);
searchInput.addEventListener('input', queueRenderProducts);
categoryInput.addEventListener('change', queueRenderProducts);
maxPriceInput.addEventListener('input', queueRenderProducts);
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
