@extends('store.layout')

@section('title', 'El Dorado - Checkout')

@section('content')
    <section class="checkout-shell">
        <section class="checkout-hero surface">
            <div class="checkout-hero-copy">
                <p class="eyebrow">Ruta de Compra</p>
                <h2 class="title">Del menu al pago en una sola experiencia mas ordenada.</h2>
            </div>

            <div class="checkout-hero-panel">
                <article class="hero-kpi-card">
                    <span class="hero-kpi-label">Estado del pedido</span>
                    <strong id="heroStatus">Listo para confirmar</strong>
                </article>
            </div>
        </section>

        <div class="checkout-board">
            <aside class="checkout-sidebar">
                <section class="surface summary-panel">
                    <div class="summary-panel-head">
                        <div>
                            <p class="eyebrow">Tu pedido</p>
                            <h3 class="section-title">Resumen del carrito</h3>
                        </div>
                        <a href="{{ route('store.products') }}" class="btn-soft">Seguir comprando</a>
                    </div>

                    <div id="cartList" class="cart-rows">Sin productos agregados.</div>

                    <div class="summary-total-box">
                        <span>Total estimado</span>
                        <strong>S/ <span id="cartTotal">0.00</span></strong>
                    </div>

                    <button type="submit" form="orderForm" class="btn-main sidebar-submit">Confirmar pedido</button>
                </section>

                <section id="lastOrderBox" class="surface recent-order-panel" style="display:none;"></section>
            </aside>

            <section class="checkout-flow">
                <form id="orderForm" class="checkout-form">
                    <input type="hidden" name="drink_note" value="">
                    <input type="hidden" name="latitude">
                    <input type="hidden" name="longitude">

                    <section class="surface form-stage">
                        <div class="stage-head">
                            <span class="stage-number">01</span>
                            <div>
                                <p class="eyebrow">Identidad del cliente</p>
                                <h3 class="section-title">Completa tus datos base</h3>
                            </div>
                        </div>

                        <div class="form-grid">
                            <label class="field-card">
                                <span class="label-main">Nombre</span>
                                <input class="input-main" name="customer_name" required placeholder="Ej: Juan Perez">
                            </label>
                            <label class="field-card">
                                <span class="label-main">Telefono</span>
                                <input class="input-main" name="customer_phone" required placeholder="Ej: 987654321">
                            </label>
                        </div>
                    </section>

                    <section class="surface form-stage payment-stage">
                        <div class="stage-head">
                            <span class="stage-number">02</span>
                            <div>
                                <p class="eyebrow">Pago</p>
                                <h3 class="section-title">Elige como pagaras</h3>
                            </div>
                        </div>

                        <label id="payOptions" class="field-card payment-method-field">
                            <span class="label-main">Metodo de pago</span>
                            <select class="select-main" name="payment_method" required>
                                <option value="izipay">Pago seguro con Izipay</option>
                                <option value="cod">Pago contraentrega</option>
                            </select>
                            <span class="field-help">Elige si pagaras ahora con Izipay o al recibir tu pedido.</span>
                        </label>

                        <div class="payment-stage-layout">
                            <div id="paymentInfo" class="payment-brief"></div>
                            <input type="hidden" name="payment_reference">
                        </div>
                    </section>

                    <section class="surface form-stage">
                        <div class="stage-head">
                            <span class="stage-number">03</span>
                            <div>
                                <p class="eyebrow">Comprobante</p>
                                <h3 class="section-title">Elige boleta, factura o compra simple</h3>
                            </div>
                        </div>

                        <div class="form-grid">
                            <label class="field-card">
                                <span class="label-main">Tipo de comprobante</span>
                                <select class="select-main" name="billing_receipt_type" id="billingReceiptType">
                                    <option value="">No deseo comprobante</option>
                                    <option value="boleta">Boleta con DNI</option>
                                    <option value="factura">Factura con RUC</option>
                                </select>
                            </label>

                            <div id="billingDocumentWrap" class="field-card" style="display:none;">
                                <span class="label-main" id="billingDocumentLabel">DNI del cliente</span>
                                <div class="lookup-row">
                                    <input class="input-main" name="billing_document_number" id="billingDocumentNumber" inputmode="numeric" placeholder="Ej: 12345678">
                                    <button type="button" id="lookupDocumentBtn" class="btn-soft">Consultar</button>
                                </div>
                            </div>
                        </div>

                        <div id="billingLookupBox" class="info-ribbon">Activa boleta o factura para identificar al cliente antes de pagar.</div>

                        <div id="billingFieldsWrap" class="form-grid extended-grid" style="display:none;">
                            <label class="field-card">
                                <span class="label-main" id="billingNameLabel">Nombre del cliente</span>
                                <input class="input-main readonly-input" name="billing_name" id="billingName" readonly>
                            </label>

                            <div class="field-card">
                                <span class="label-main">Entrega del comprobante</span>
                                <div class="choice-cluster">
                                    <label class="choice-chip"><input type="radio" name="receipt_delivery" value="download" checked> Descargar aqui</label>
                                    <label class="choice-chip"><input type="radio" name="receipt_delivery" value="registered_email"> A mi correo registrado</label>
                                    <label class="choice-chip"><input type="radio" name="receipt_delivery" value="other_email"> A otro correo</label>
                                </div>
                            </div>

                            <label id="billingEmailWrap" class="field-card" style="display:none;">
                                <span class="label-main">Correo destino</span>
                                <input class="input-main" name="billing_email" id="billingEmail" type="email" placeholder="Ej: cliente@correo.com">
                            </label>
                        </div>
                        <input type="hidden" name="billing_document_type" id="billingDocumentType">
                        <input type="hidden" name="billing_metadata" id="billingMetadata">
                    </section>

                    <section class="surface form-stage">
                        <div class="stage-head">
                            <span class="stage-number">04</span>
                            <div>
                                <p class="eyebrow">Entrega</p>
                                <h3 class="section-title">Elige recojo o delivery</h3>
                            </div>
                        </div>

                        <div class="form-grid">
                            <label class="field-card">
                                <span class="label-main">Tipo de entrega</span>
                                <select class="select-main" name="delivery_type" id="deliveryType">
                                    <option value="pickup">Recojo en local</option>
                                    <option value="delivery">Delivery</option>
                                </select>
                            </label>
                            <div class="field-card geo-card">
                                <span class="label-main">Ubicacion asistida</span>
                                <button id="geoBtn" type="button" class="btn-soft geo-btn">Usar mi ubicacion exacta</button>
                                <p class="field-help">Solo para delivery. Tomamos tu punto y te ayudamos a completar la referencia.</p>
                            </div>
                        </div>

                        <div id="deliveryFieldsWrap" class="form-grid extended-grid" style="display:none;">
                            <label class="field-card">
                                <span class="label-main">Ubicacion exacta</span>
                                <input class="input-main" name="address" placeholder="Ej: Av. Huancavelica 123, frente al parque">
                            </label>
                            <label class="field-card">
                                <span class="label-main">Entre calles o referencia</span>
                                <input class="input-main" name="reference" placeholder="Ej: entre Jr. Lima y Jr. Ayacucho">
                            </label>
                        </div>

                        <div id="geoMsg" class="info-ribbon" style="display:none;"></div>
                    </section>

                    <section id="saladWrap" class="surface form-stage" style="display:none;">
                        <div class="stage-head">
                            <span class="stage-number">05</span>
                            <div>
                                <p class="eyebrow">Pollo a la brasa</p>
                                <h3 class="section-title">Define tu preferencia de ensalada</h3>
                            </div>
                        </div>

                        <div class="form-grid">
                            <label class="field-card">
                                <span class="label-main">Tipo de ensalada</span>
                                <select class="select-main" name="salad_type">
                                    <option value="">Selecciona...</option>
                                    <option value="dulce">Dulce</option>
                                    <option value="salada">Salada</option>
                                </select>
                            </label>
                        </div>
                    </section>

                    <div class="checkout-actions">
                        <button type="submit" class="btn-main checkout-submit">Confirmar pedido</button>
                        <div id="orderMsg" class="order-feedback"></div>
                    </div>
                </form>
            </section>
        </div>

        <div id="processingOverlay" class="processing-overlay">
            <div class="processing-card">
                <img src="/images/ui/processing-chicken.png" alt="Procesando pedido" class="processing-image">
                <h3 id="processingTitle">Espera, estamos procesando tu pedido</h3>
                <p id="processingText">Validando productos, datos de entrega y forma de pago.</p>
            </div>
        </div>
    </section>

    <style>
        .checkout-shell,.checkout-form,.checkout-flow{display:grid;gap:14px}
        .checkout-hero{display:grid;grid-template-columns:1.12fr .88fr;gap:14px;padding:16px}
        .checkout-hero-copy{display:grid;gap:18px;align-content:center}
        .hero-copy-text{max-width:560px;font-size:15px}
        .checkout-highlights,.rule-cloud,.proof-guidelines{display:flex;flex-wrap:wrap;gap:10px}
        .checkout-highlights span,.rule-cloud span,.proof-guidelines span{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:999px;border:1px solid rgba(234,182,138,.8);background:rgba(255,247,240,.86);color:#82471f;font-size:12px;font-weight:900}
        .checkout-hero-panel{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .hero-kpi-card{display:grid;gap:8px;padding:14px;border-radius:18px;border:1px solid rgba(234,182,138,.76);background:linear-gradient(180deg,rgba(255,255,255,.95),rgba(255,245,236,.92));box-shadow:0 12px 24px rgba(52,17,0,.08)}
        .hero-kpi-wide{grid-column:1/-1}
        .hero-kpi-label{font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#9b5a2c;font-weight:900}
        .hero-kpi-card strong{font-size:22px;color:#2d1708}
        .hero-kpi-card p{margin:0;color:var(--ink-soft);font-size:13px;line-height:1.55}
        .checkout-board{display:grid;grid-template-columns:.9fr 1.1fr;gap:14px;align-items:start}
        .checkout-sidebar{position:sticky;top:96px;display:grid;gap:14px}
        .summary-panel,.form-stage{padding:16px;display:grid;gap:14px}
        .summary-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px}
        .cart-rows{display:grid;gap:12px}
        .cart-item-card{display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;padding:14px;border-radius:22px;border:1px solid rgba(234,182,138,.72);background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(255,245,237,.9))}
        .cart-item-index{width:44px;height:44px;border-radius:16px;display:grid;place-items:center;font-size:13px;font-weight:900;color:#7b451f;background:linear-gradient(120deg,rgba(255,111,31,.18),rgba(255,157,90,.24));border:1px solid rgba(234,182,138,.84)}
        .cart-item-main,.payment-support-stack{display:grid;gap:4px}
        .cart-item-name{font-weight:900;color:#2c1708}
        .cart-item-meta{color:var(--ink-soft);font-size:12px;text-transform:capitalize}
        .cart-item-actions{display:grid;justify-items:end;gap:8px}
        .cart-item-line{font-weight:900;color:#8d3d00}
        .qty-actions{display:inline-flex;align-items:center;gap:6px;padding:6px;border-radius:999px;border:1px solid rgba(234,182,138,.7);background:rgba(255,250,246,.92)}
        .qty-read{min-width:24px;text-align:center;font-weight:900;color:#6b3916}
        .qty-btn{width:30px;height:30px;border:0;border-radius:999px;cursor:pointer;background:linear-gradient(120deg,var(--orange),var(--orange-soft));color:#2c1406;font-size:18px;line-height:1;box-shadow:0 8px 16px rgba(255,111,31,.18)}
        .summary-total-box{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-radius:24px;background:#23150d;color:#fff1e8}
        .summary-total-box span{font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,241,232,.74)}
        .summary-total-box strong{font-size:32px}
        .sidebar-submit{width:100%;min-height:54px;font-size:15px}
        .summary-note-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
        .summary-note,.recent-order-panel,.field-card,.proof-panel,.payment-brief{display:grid;gap:10px;padding:16px;border-radius:22px;border:1px solid rgba(234,182,138,.72);background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(255,247,240,.88))}
        .summary-note strong,.recent-order-panel strong{display:block;margin-bottom:6px;color:#8d3d00}
        .summary-note p,.recent-order-panel p,.field-help,.proof-panel-head p,.proof-preview-note{margin:0;color:var(--ink-soft);font-size:13px;line-height:1.55}
        .recent-order-panel a{color:#8d3d00;font-weight:900}
        .stage-head{display:grid;grid-template-columns:auto 1fr;gap:14px;align-items:start}
        .stage-number{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;font-size:13px;font-weight:900;color:#6f3916;background:linear-gradient(120deg,rgba(255,111,31,.18),rgba(255,157,90,.26));border:1px solid rgba(234,182,138,.84)}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
        .extended-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
        .lookup-row{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center}
        .info-ribbon{padding:14px 16px;border-radius:20px;border:1px dashed rgba(234,182,138,.9);background:rgba(255,247,240,.78);color:#6f431f;font-size:13px;line-height:1.55}
        .readonly-input{background:#fff6ee}
        .choice-cluster{display:flex;flex-wrap:wrap;gap:10px}
        .choice-chip{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;border:1px solid rgba(234,182,138,.78);background:rgba(255,249,245,.92);color:#6f431f;font-size:13px;font-weight:700}
        .geo-btn{justify-self:start}
        .pay-stage-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
        .pay-tile{position:relative;display:grid;grid-template-columns:auto 1fr;column-gap:10px;row-gap:4px;align-items:center;min-height:92px;padding:12px;border-radius:16px;border:1px solid rgba(234,182,138,.72);background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(255,247,240,.88));cursor:pointer;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
        .pay-tile:hover,.pay-tile.is-active{transform:translateY(-2px);border-color:rgba(255,111,31,.42);box-shadow:0 14px 28px rgba(255,111,31,.12)}
        .pay-tile input{position:absolute;top:12px;right:12px}
        .pay-icon{width:36px;height:36px;border-radius:12px;display:grid;place-items:center;border:1px solid rgba(234,182,138,.75);background:rgba(255,247,240,.88);color:#8d3d00;box-shadow:0 8px 16px rgba(255,111,31,.10)}
        .pay-tile-body{display:grid;gap:3px}
        .pay-tile-kicker{font-size:10px;letter-spacing:.13em;text-transform:uppercase;color:#9b5a2c;font-weight:900}
        .pay-tile strong{font-size:19px;color:#2d1708}
        .pay-tile small{color:var(--ink-soft);font-size:11px;line-height:1.35}
        .payment-stage-layout{display:grid;grid-template-columns:minmax(220px,.8fr) minmax(260px,1.2fr);gap:12px}
        .payment-brief{align-content:start;min-height:auto}
        .payment-brief strong{font-size:20px;color:#8d3d00}
        .payment-brief .qr-image{width:min(100%,150px);aspect-ratio:1/1;object-fit:contain;border-radius:14px;border:1px dashed rgba(255,111,31,.35);background:#fff;padding:8px}
        .checkout-actions{display:grid;gap:10px;padding-bottom:8px}
        .checkout-submit{min-height:58px;font-size:16px}
        .order-feedback{min-height:22px;color:#7b3d11;font-size:14px;line-height:1.5}
        .processing-overlay{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:18px;background:radial-gradient(circle at top left,rgba(255,157,90,.28),transparent 28%),radial-gradient(circle at bottom right,rgba(255,111,31,.18),transparent 26%),linear-gradient(180deg,#fffaf6 0%,#fff1e5 44%,#ffead8 100%);z-index:90}
        .processing-overlay::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at center,rgba(255,111,31,.12),transparent 42%);pointer-events:none}
        .processing-card{position:relative;width:min(92vw,420px);border-radius:28px;padding:28px 22px;border:1px solid rgba(234,182,138,.82);background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(255,246,238,.98));box-shadow:0 26px 60px rgba(52,17,0,.13);text-align:center;display:grid;gap:12px;justify-items:center;color:#25170f}
        .processing-image{width:86px;height:86px;object-fit:contain;border-radius:22px;border:1px solid rgba(234,182,138,.82);background:#fffdf9;animation:processingPulse 1.1s ease-in-out infinite}
        .processing-card h3{margin:0;color:#25170f;font-size:21px;line-height:1.2}
        .processing-card p{margin:0;color:#68432e;font-size:14px;line-height:1.45;font-weight:800}
        @keyframes processingPulse{0%,100%{transform:scale(1);opacity:.92}50%{transform:scale(1.06);opacity:1}}
        .checkout-shell *{min-width:0}
        .checkout-shell .title,.section-title,.hero-kpi-card strong,.cart-item-name,.pay-tile strong,.payment-brief strong{overflow-wrap:anywhere;line-height:1.12}
        .checkout-shell .title{font-size:clamp(30px,4.1vw,48px)}
        .section-title{font-size:clamp(22px,2.2vw,30px)}
        .checkout-board{grid-template-columns:minmax(330px,.82fr) minmax(0,1.18fr)}
        .checkout-flow{min-width:0}
        .form-stage{overflow:hidden}
        .field-card,.proof-panel,.payment-brief{min-width:0}
        .pay-stage-grid{grid-template-columns:repeat(2,minmax(190px,1fr))}
        .pay-tile{min-height:86px;align-items:center}
        .pay-tile strong{font-size:clamp(17px,1.7vw,20px)}
        .pay-tile small{overflow-wrap:break-word}
        .payment-stage-layout{grid-template-columns:minmax(220px,.86fr) minmax(260px,1.14fr);align-items:start}
        .choice-cluster{display:grid;grid-template-columns:repeat(3,minmax(0,1fr))}
        .choice-chip{white-space:normal;line-height:1.25}
        @media (max-width:1120px){.checkout-board,.checkout-hero,.payment-stage-layout{grid-template-columns:1fr}.checkout-sidebar{position:static}.extended-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.pay-stage-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media (max-width:760px){.checkout-shell{gap:14px}.checkout-hero{padding:16px}.checkout-hero-panel,.form-grid,.extended-grid,.summary-note-grid,.lookup-row{grid-template-columns:1fr}.summary-panel-head,.cart-item-card{grid-template-columns:1fr}.cart-item-actions{justify-items:start}.choice-cluster{grid-template-columns:1fr}.pay-stage-grid{grid-template-columns:1fr}.pay-tile{grid-template-columns:auto minmax(0,1fr);min-height:auto;padding:14px}.summary-total-box strong{font-size:24px}.checkout-shell .title{font-size:clamp(28px,8vw,42px)}}
        .hero-kpi-card strong,
        .pay-tile strong{color:var(--panel-ink)}
        .checkout-shell .surface,.checkout-shell .form-stage,.checkout-shell .summary-panel,.checkout-shell .field-card,.checkout-shell .pay-tile,.checkout-shell .payment-brief,.checkout-shell .hero-kpi-card,.checkout-shell .info-ribbon{color:#25170f!important;text-shadow:none!important}
        .checkout-shell .surface *,.checkout-shell .form-stage *,.checkout-shell .summary-panel *,.checkout-shell .field-card *,.checkout-shell .pay-tile *,.checkout-shell .payment-brief *,.checkout-shell .hero-kpi-card *,.checkout-shell .info-ribbon *{text-shadow:none!important}
        .checkout-shell .section-title,.checkout-shell .title,.checkout-shell .hero-kpi-card strong,.checkout-shell .pay-tile strong,.checkout-shell .payment-brief strong,.checkout-shell .cart-item-name{color:#25170f!important}
        .checkout-shell .eyebrow,.checkout-shell .label-main,.checkout-shell .hero-kpi-label,.checkout-shell .pay-tile-kicker{color:#9b5a2c!important}
        .checkout-shell .field-help,.checkout-shell .pay-tile small,.checkout-shell .payment-brief div,.checkout-shell .cart-item-meta{color:#68432e!important}
    </style>
@endsection

@section('scripts')
<script>
const COMPANY={brand_name:'Pollos y Parrillas El Dorado',payments:{izipay:{label:'Pago seguro con Izipay',message:'Paga con tarjeta desde el checkout seguro de Izipay.',public_key:''},cod:{label:'Pago contraentrega',message:'Paga al recibir tu pedido en el lugar de entrega acordado.',enabled:true}}};
const cartListEl=document.getElementById('cartList'),cartTotalEl=document.getElementById('cartTotal'),orderForm=document.getElementById('orderForm'),orderMsg=document.getElementById('orderMsg'),payOptions=document.getElementById('payOptions'),paymentInfo=document.getElementById('paymentInfo'),saladWrap=document.getElementById('saladWrap'),lastOrderBox=document.getElementById('lastOrderBox'),processingOverlay=document.getElementById('processingOverlay'),processingTitle=document.getElementById('processingTitle'),processingText=document.getElementById('processingText'),geoBtn=document.getElementById('geoBtn'),geoMsg=document.getElementById('geoMsg'),paymentProofWrap=document.getElementById('paymentProofWrap'),paymentProofFile=document.getElementById('paymentProofFile'),paymentProofPreview=document.getElementById('paymentProofPreview'),billingReceiptType=document.getElementById('billingReceiptType'),billingDocumentType=document.getElementById('billingDocumentType'),billingMetadata=document.getElementById('billingMetadata'),billingDocumentWrap=document.getElementById('billingDocumentWrap'),billingDocumentLabel=document.getElementById('billingDocumentLabel'),billingDocumentNumber=document.getElementById('billingDocumentNumber'),lookupDocumentBtn=document.getElementById('lookupDocumentBtn'),billingLookupBox=document.getElementById('billingLookupBox'),billingFieldsWrap=document.getElementById('billingFieldsWrap'),billingName=document.getElementById('billingName'),billingEmail=document.getElementById('billingEmail'),billingNameLabel=document.getElementById('billingNameLabel'),billingEmailWrap=document.getElementById('billingEmailWrap'),deliveryType=document.getElementById('deliveryType'),deliveryFieldsWrap=document.getElementById('deliveryFieldsWrap'),heroStatus=document.getElementById('heroStatus');let lastLookupValue='';
function getToken(){return localStorage.getItem('ed_token')}function isLoggedIn(){return Boolean(getToken())}function getCart(){return JSON.parse(localStorage.getItem('ed_cart')||'[]')}function setCart(cart){localStorage.setItem('ed_cart',JSON.stringify(cart));window.dispatchEvent(new Event('storage'))}function money(n){return Number(n).toFixed(2)}function digits(v){return String(v||'').replace(/\D/g,'')}function optionalTrim(field){return field?field.value.trim()||null:null}function needsDigitalProof(method){return false}
const PURCHASE_LIMITS={exact:{'pollo entero a la brasa':1,'mega combo familiar':1,'1/2 pollo a la brasa':2,'1/4 pollo a la brasa':4,'mostrito tradicional':4,'chicha morada 1l':2,'limonada frozen':2},sodaNames:['coca-cola personal 500ml','inca kola personal 500ml','sprite personal 500ml'],sodaMax:3};
function setProcessingState(visible,title='Espera, estamos procesando tu pedido',text='Validando productos, datos de entrega y forma de pago.'){processingOverlay.style.display=visible?'flex':'none';processingTitle.textContent=title;processingText.textContent=text}
function hasChickenInCart(){return getCart().some(item=>String(item.category||'').toLowerCase()==='pollos')}
function normalizeProductName(name){return String(name||'').trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')}
function validateCartLimits(cart){const totals={};let sodaTotal=0;cart.forEach(item=>{const normalizedName=normalizeProductName(item.name),quantity=Number(item.qty||0);totals[normalizedName]=(totals[normalizedName]||0)+quantity;if(PURCHASE_LIMITS.sodaNames.includes(normalizedName))sodaTotal+=quantity});for(const[name,max]of Object.entries(PURCHASE_LIMITS.exact)){if((totals[name]||0)>max){const label=cart.find(item=>normalizeProductName(item.name)===name)?.name||name;return`Solo se permiten ${max} unidades de ${label} por pedido.`}}if(sodaTotal>PURCHASE_LIMITS.sodaMax)return`Solo se permiten ${PURCHASE_LIMITS.sodaMax} gaseosas personales por pedido.`;return null}
 function paymentMethod(){return orderForm.elements.payment_method?.value||''}
function updateHeroStatus(cart){heroStatus.textContent=!cart.length?'Carrito vacio':needsDigitalProof(paymentMethod())?'Listo para pagar con voucher':'Listo para confirmar'}
function renderCart(){const cart=getCart();if(!cart.length){cartListEl.textContent='Sin productos agregados.';cartTotalEl.textContent='0.00';saladWrap.style.display='none';updateHeroStatus(cart);return}let total=0;cartListEl.innerHTML=cart.map((item,index)=>{const line=Number(item.price)*Number(item.qty),promo=item.promo_id?`<br><strong style="color:#c94700">Promoción: antes S/ ${money(item.original_price||item.price)} · ahora S/ ${money(item.price)}</strong>`:'';total+=line;return`<article class="cart-item-card"><div class="cart-item-index">#${index+1}</div><div class="cart-item-main"><strong class="cart-item-name">${item.name}</strong><div class="cart-item-meta">${item.category||'general'} - S/ ${money(item.price)} c/u${promo}</div></div><div class="cart-item-actions"><strong class="cart-item-line">S/ ${money(line)}</strong><div class="qty-actions"><button data-minus="${item.id}" class="qty-btn" type="button">-</button><span class="qty-read">${item.qty}</span><button data-plus="${item.id}" class="qty-btn" type="button">+</button></div></div></article>`}).join('');cartTotalEl.textContent=money(total);saladWrap.style.display=hasChickenInCart()?'block':'none';updateHeroStatus(cart);cartListEl.querySelectorAll('[data-minus]').forEach(btn=>btn.addEventListener('click',()=>changeQty(Number(btn.getAttribute('data-minus')),-1)));cartListEl.querySelectorAll('[data-plus]').forEach(btn=>btn.addEventListener('click',()=>changeQty(Number(btn.getAttribute('data-plus')),1)))}
function changeQty(productId,delta){const cart=getCart(),item=cart.find(i=>i.id===productId);if(!item)return;const nextCart=cart.map(entry=>({...entry})),nextItem=nextCart.find(i=>i.id===productId);nextItem.qty+=delta;const limitError=validateCartLimits(nextCart.filter(i=>i.qty>0));if(limitError){orderMsg.textContent=limitError;return}item.qty+=delta;setCart(cart.filter(i=>i.qty>0));renderCart()}
function currentReceiptType(){return billingReceiptType.value}function currentDocumentLength(){return currentReceiptType()==='factura'?11:8}function resetBillingFields(){billingDocumentNumber.value='';billingName.value='';billingEmail.value='';billingMetadata.value='';lastLookupValue=''}
function updateBillingUi(){const receipt=currentReceiptType();if(!receipt){billingDocumentType.value='';billingDocumentWrap.style.display='none';billingFieldsWrap.style.display='none';billingEmailWrap.style.display='none';billingLookupBox.textContent='Activa boleta o factura para identificar al cliente antes de pagar.';resetBillingFields();return}const isFactura=receipt==='factura';billingDocumentWrap.style.display='grid';billingFieldsWrap.style.display='grid';billingDocumentType.value=isFactura?'ruc':'dni';billingDocumentLabel.textContent=isFactura?'RUC del cliente':'DNI del cliente';billingDocumentNumber.placeholder=isFactura?'Ej: 20131312955':'Ej: 12345678';billingNameLabel.textContent=isFactura?'Razon social':'Nombre del cliente';billingLookupBox.textContent=isFactura?'Ingresa solo el RUC y consultamos la razon social para emitir factura.':'Ingresa solo el DNI y consultamos automaticamente al cliente para emitir boleta.';resetBillingFields();updateReceiptDeliveryUi()}
function isValidRuc(number){if(!/^\d{11}$/.test(number))return false;const weights=[5,4,3,2,7,6,5,4,3,2];let sum=0;for(let i=0;i<10;i++)sum+=Number(number[i])*weights[i];let digit=11-(sum%11);if(digit===10)digit=0;if(digit===11)digit=1;return digit===Number(number[10])}
async function lookupDocument(){const token=getToken(),docType=billingDocumentType.value,number=digits(billingDocumentNumber.value),needed=currentDocumentLength(),label=docType==='ruc'?'RUC':'DNI';if(!token){billingLookupBox.textContent=`Debes iniciar sesion para validar el ${label}.`;return}if(!docType||!number){billingLookupBox.textContent=`Selecciona comprobante e ingresa el ${label}.`;return}if(number.length!==needed){billingLookupBox.textContent=`El ${label} debe tener ${needed} digitos.`;return}if(docType==='ruc'&&!isValidRuc(number)){billingLookupBox.textContent='El RUC ingresado no es valido. Revisa los 11 digitos antes de consultar.';return}billingLookupBox.textContent='Consultando documento...';try{const endpoint=docType==='ruc'?'/api/v1/lookups/ruc':'/api/v1/lookups/dni',body=docType==='ruc'?{ruc:number}:{dni:number},res=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/json','Authorization':`Bearer ${token}`},body:JSON.stringify(body)}),data=await res.json();if(!res.ok){billingLookupBox.textContent=data.message||'No se pudo consultar el documento.';return}const normalized=data.normalized||{};billingName.value=docType==='ruc'?(normalized.business_name||''):(normalized.full_name||'');if(!orderForm.customer_name.value.trim())orderForm.customer_name.value=billingName.value;billingMetadata.value=JSON.stringify({normalized});billingLookupBox.textContent=billingName.value?`${label} identificado. Ya puedes emitir ${currentReceiptType()==='factura'?'factura':'boleta'}.`:`No se encontraron datos para ese ${label}.`;lastLookupValue=number}catch{billingLookupBox.textContent='No se pudo conectar al servidor para validar el documento.'}}
function selectedReceiptDelivery(){const checked=orderForm.querySelector('input[name="receipt_delivery"]:checked');return checked?checked.value:'download'}
function updateReceiptDeliveryUi(){const mode=selectedReceiptDelivery(),currentUser=typeof window.parseUser==='function'?window.parseUser():null;if(!currentReceiptType()){billingEmailWrap.style.display='none';billingEmail.value='';return}if(mode==='download'){billingEmailWrap.style.display='none';billingEmail.value='';return}billingEmailWrap.style.display='grid';if(mode==='registered_email'){billingEmail.value=currentUser?.email||'';billingEmail.readOnly=true}else{billingEmail.readOnly=false;if(billingEmail.value===currentUser?.email)billingEmail.value=''}}
function updateDeliveryUi(){const isDelivery=deliveryType.value==='delivery';deliveryFieldsWrap.style.display=isDelivery?'grid':'none';geoBtn.style.display=isDelivery?'inline-flex':'none';geoMsg.style.display='none';if(!isDelivery){orderForm.address.value='';orderForm.reference.value='';if(orderForm.latitude)orderForm.latitude.value='';if(orderForm.longitude)orderForm.longitude.value=''}}
async function reverseGeocode(latitude,longitude){const controller=new AbortController(),timeout=setTimeout(()=>controller.abort(),8000),url=`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(latitude)}&lon=${encodeURIComponent(longitude)}&zoom=18&addressdetails=1`;try{const res=await fetch(url,{headers:{'Accept':'application/json'},signal:controller.signal});if(!res.ok)throw new Error('No se pudo traducir la ubicacion a calles cercanas.');return res.json()}finally{clearTimeout(timeout)}}
async function loadCompanySettings(){try{const res=await fetch('/api/v1/settings/public'),data=await res.json();if(res.ok&&data&&data.payments){COMPANY.brand_name=data.brand_name||COMPANY.brand_name;COMPANY.payments=data.payments}}catch{}}
 function updatePaymentInfo(){const method=paymentMethod(),settings=COMPANY.payments?.[method]||{},isIzipay=method==='izipay';if(paymentProofWrap)paymentProofWrap.style.display='none';updateHeroStatus(getCart());paymentInfo.innerHTML=method?`<span class="hero-kpi-label">Pago elegido</span><strong>${settings.label||(isIzipay?'Pago seguro con Izipay':'Pago contraentrega')}</strong><div>${settings.message||(isIzipay?'Paga con tarjeta desde el checkout seguro de Izipay.':'Paga al recibir tu pedido en el lugar de entrega acordado.')}</div>${isIzipay?'<div>No compartes los datos de tu tarjeta con esta web.</div>':'<div>Ten listo el monto al momento de la entrega.</div>'}`:'<span class="hero-kpi-label">Pago</span><strong>Sin metodo seleccionado</strong>'}
 function resetCheckoutAfterLogout(){orderForm.reset();resetBillingFields();billingReceiptType.value='';deliveryType.value='pickup';if(paymentProofFile)paymentProofFile.value='';if(paymentProofPreview){paymentProofPreview.removeAttribute('src');paymentProofPreview.style.display='none'}lastOrderBox.style.display='none';lastOrderBox.innerHTML='';paymentInfo.innerHTML='<span class="hero-kpi-label">Pago</span><strong>Sin metodo seleccionado</strong>';orderMsg.textContent='';renderCart();updateBillingUi();updateDeliveryUi()}
 window.addEventListener('ed:customer-session-cleared',resetCheckoutAfterLogout);
 window.addEventListener('pageshow',event=>{if(event.persisted&&!isLoggedIn())resetCheckoutAfterLogout()});
async function openIzipayCheckout(orderId){const token=getToken(),res=await fetch(`/api/v1/orders/${orderId}/payments/izipay-checkout`,{headers:{'Authorization':`Bearer ${token}`}}),data=await res.json();if(!res.ok)throw new Error(data.message||'No se pudo iniciar Izipay.');const checkoutUrl=data.payment_url;if(!checkoutUrl)throw new Error('Izipay aun no esta configurado en el servidor.');window.location.href=checkoutUrl}
function showLastOrder(){const tracking=localStorage.getItem('ed_last_tracking');if(!tracking)return;lastOrderBox.style.display='block';lastOrderBox.innerHTML=`<strong>Ultimo pedido: ${tracking}</strong><p>Tu ultimo codigo queda guardado para que puedas volver a seguirlo sin buscarlo otra vez.</p><a href="/mis-pedidos">Ver seguimiento en Mis pedidos</a>`}
function applyPolliaCheckoutPrefill(){try{const raw=localStorage.getItem('ed_pollia_checkout_prefill_v1');if(!raw)return;const data=JSON.parse(raw);if(data.customer_name&&!orderForm.customer_name.value.trim())orderForm.customer_name.value=data.customer_name;if(data.phone&&!orderForm.customer_phone.value.trim())orderForm.customer_phone.value=data.phone;if(data.delivery_type)orderForm.delivery_type.value=data.delivery_type;if(data.address&&!orderForm.address.value.trim()){orderForm.delivery_type.value='delivery';orderForm.address.value=data.address}if(data.reference&&!orderForm.reference.value.trim())orderForm.reference.value=data.reference;if(data.latitude&&orderForm.latitude)orderForm.latitude.value=data.latitude;if(data.longitude&&orderForm.longitude)orderForm.longitude.value=data.longitude;if(data.payment_method&&['izipay','cod'].includes(data.payment_method))orderForm.elements.payment_method.value=data.payment_method;if(data.payment_reference&&!orderForm.payment_reference.value.trim())orderForm.payment_reference.value=data.payment_reference;if(data.salad_type&&orderForm.salad_type)orderForm.salad_type.value=data.salad_type;if(data.billing_receipt_type)orderForm.billing_receipt_type.value=data.billing_receipt_type;updateBillingUi();if(data.billing_document_type)billingDocumentType.value=data.billing_document_type;if(data.billing_document_number&&!billingDocumentNumber.value.trim())billingDocumentNumber.value=data.billing_document_number;if(data.billing_name&&!billingName.value.trim())billingName.value=data.billing_name;if(data.email&&!billingEmail.value.trim())billingEmail.value=data.email;updateDeliveryUi();updatePaymentInfo();localStorage.removeItem('ed_pollia_checkout_prefill_v1')}catch{}}
orderForm.elements.payment_method.addEventListener('change',updatePaymentInfo);orderForm.querySelectorAll('input[name="receipt_delivery"]').forEach(radio=>radio.addEventListener('change',updateReceiptDeliveryUi));
geoBtn.addEventListener('click',()=>{if(!navigator.geolocation){geoMsg.style.display='block';geoMsg.textContent='Tu navegador no soporta geolocalizacion.';return}geoMsg.style.display='block';geoMsg.textContent='Detectando tu ubicacion exacta...';navigator.geolocation.getCurrentPosition(async position=>{const latitude=position.coords.latitude.toFixed(7),longitude=position.coords.longitude.toFixed(7);if(orderForm.latitude)orderForm.latitude.value=latitude;if(orderForm.longitude)orderForm.longitude.value=longitude;try{const data=await reverseGeocode(latitude,longitude),address=data.address||{},road=address.road||address.pedestrian||address.residential||address.cycleway||'',avenue=address.avenue||'',houseNumber=address.house_number||'',suburb=address.suburb||address.neighbourhood||address.city_district||'',city=address.city||address.town||address.village||address.county||'',state=address.state||'',amenity=address.amenity||address.shop||address.tourism||'',exactPlace=[road||avenue,houseNumber].filter(Boolean).join(' ').trim()||data.name||data.display_name||'Ubicacion detectada',nearbyReference=[amenity?`Cerca de ${amenity}`:'',suburb?`Zona ${suburb}`:'',city?`Distrito/Ciudad ${city}`:'',state&&state!==city?state:''].filter(Boolean).join(' | ');orderForm.address.value=exactPlace;orderForm.reference.value=nearbyReference||'Ubicacion obtenida desde GPS';geoMsg.textContent=`Ubicacion detectada: ${exactPlace}${nearbyReference?` | ${nearbyReference}`:''}`}catch(error){orderForm.address.value='Ubicacion detectada desde GPS';orderForm.reference.value='Completa la calle, avenida o referencia cercana manualmente';geoMsg.textContent=error?.name==='AbortError'?'La traduccion a nombre de calles tardo demasiado. Completa la referencia manualmente.':'Se detecto tu ubicacion, pero no se pudo traducir a nombres de calles. Completa la referencia manualmente.'}},()=>{geoMsg.style.display='block';geoMsg.textContent='No se pudo obtener tu ubicacion.'},{enableHighAccuracy:true,timeout:12000,maximumAge:0})});
orderForm.addEventListener('submit', async e => {
    e.preventDefault();
    if (!isLoggedIn()) {
        window.location.href = '/login';
        return;
    }

    const cart = getCart();
    if (!cart.length) {
        orderMsg.textContent = 'Tu carrito esta vacio.';
        return;
    }

    const limitError = validateCartLimits(cart);
    if (limitError) {
        orderMsg.textContent = limitError;
        return;
    }

    let billingMeta = null;
    try {
        billingMeta = billingMetadata.value ? JSON.parse(billingMetadata.value) : null;
    } catch {
        billingMeta = null;
    }

    const payload = {
        customer_name: orderForm.customer_name.value.trim(),
        customer_phone: orderForm.customer_phone.value.trim(),
        customer_email: (typeof window.parseUser === 'function' ? window.parseUser()?.email : null) || null,
        delivery_type: orderForm.delivery_type.value,
        payment_method: paymentMethod(),
        payment_reference: orderForm.payment_reference.value.trim() || null,
        billing_receipt_type: orderForm.billing_receipt_type.value || null,
        billing_document_type: orderForm.billing_document_type.value || null,
        billing_document_number: digits(orderForm.billing_document_number.value) || null,
        billing_name: orderForm.billing_name.value.trim() || null,
        billing_email: orderForm.billing_email.value.trim() || null,
        billing_address: optionalTrim(orderForm.billing_address),
        billing_metadata: billingMeta,
        salad_type: orderForm.salad_type ? (orderForm.salad_type.value || null) : null,
        drink_note: orderForm.drink_note.value.trim() || null,
        address: orderForm.address.value.trim() || null,
        reference: orderForm.reference.value.trim() || null,
        latitude: optionalTrim(orderForm.latitude),
        longitude: optionalTrim(orderForm.longitude),
        items: cart.map(i => ({ product_id: i.id, quantity: i.qty, promotion_id: i.promo_id || null })),
    };

    setProcessingState(true);
    orderMsg.textContent = 'Procesando pedido...';

    try {
        const res = await fetch('/api/v1/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getToken()}`,
            },
            body: JSON.stringify(payload),
        });

        const raw = await res.text();
        let data = {};
        try {
            data = raw ? JSON.parse(raw) : {};
        } catch {
            data = {};
        }

        if (!res.ok) {
            setProcessingState(false);
            orderMsg.textContent = data.message || (raw && raw.includes('Server Error')
                ? 'El servidor tuvo un error al registrar el pedido.'
                : 'No se pudo crear el pedido.');
            return;
        }

        if (paymentMethod() === 'izipay') {
            setProcessingState(true, 'Pedido creado, redirigiendo a Izipay', 'Tu pedido ya esta registrado. Ahora completaremos el pago seguro.');
            try {
                await openIzipayCheckout(data.id);
                return;
            } catch (error) {
                setProcessingState(false);
                orderMsg.textContent = `Pedido creado con codigo ${data.tracking_code}, pero no se pudo abrir Izipay: ${error.message}`;
                return;
            }
        }

        localStorage.setItem('ed_last_tracking', data.tracking_code);
        const recent = JSON.parse(localStorage.getItem('ed_recent_trackings') || '[]');
        localStorage.setItem('ed_recent_trackings', JSON.stringify([data.tracking_code, ...recent.filter(v => v !== data.tracking_code)].slice(0, 10)));
        setProcessingState(true, 'Pedido enviado a la empresa', `Tu pedido (${data.tracking_code}) fue enviado al sistema. Te avisaremos cuando cambie de estado.`);
        orderMsg.textContent = `Pedido creado. Codigo: ${data.tracking_code}. Estado: ${data.status || 'pending'}`;
        setCart([]);
        renderCart();
        orderForm.reset();
        if(paymentProofPreview)paymentProofPreview.textContent = 'Aun no seleccionaste archivo.';
        updatePaymentInfo();
        updateBillingUi();
        updateDeliveryUi();
        showLastOrder();
        setTimeout(() => {
            setProcessingState(false);
            window.location.href = '/mis-pedidos';
        }, 1600);
    } catch {
        setProcessingState(false);
        orderMsg.textContent = 'No se pudo conectar al servidor.';
    }
});
 renderCart();billingReceiptType.addEventListener('change',updateBillingUi);deliveryType.addEventListener('change',updateDeliveryUi);billingDocumentNumber.addEventListener('input',()=>{billingDocumentNumber.value=digits(billingDocumentNumber.value).slice(0,currentDocumentLength());const needed=currentDocumentLength(),current=billingDocumentNumber.value.length;if(currentReceiptType())billingLookupBox.textContent=current<needed?`Faltan ${needed-current} digitos para consultar el ${billingDocumentType.value.toUpperCase()}.`:billingLookupBox.textContent;if(current===needed&&billingDocumentNumber.value!==lastLookupValue)lookupDocument()});lookupDocumentBtn.addEventListener('click',()=>lookupDocument());if(paymentProofFile)paymentProofFile.addEventListener('change',()=>{const file=paymentProofFile.files?.[0];paymentProofPreview.textContent=file?`Archivo listo: ${file.name} (${Math.round(file.size/1024)} KB)`:'Aun no seleccionaste archivo.';updateHeroStatus(getCart())});updateBillingUi();if(isLoggedIn())applyPolliaCheckoutPrefill();else resetCheckoutAfterLogout();updateDeliveryUi();loadCompanySettings().finally(()=>{updatePaymentInfo();updateReceiptDeliveryUi();if(isLoggedIn())showLastOrder();updateHeroStatus(getCart())});
</script>
<script>
const guidedOrderNote = sessionStorage.getItem('ed_guided_order_note') || '';
if (document.getElementById('orderForm')?.drink_note) {
    document.getElementById('orderForm').drink_note.value = guidedOrderNote.slice(0, 120);
}
sessionStorage.removeItem('ed_guided_order_note');
</script>
@endsection
