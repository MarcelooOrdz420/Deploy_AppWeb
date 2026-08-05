@extends('store.layout')

@section('title', 'El Dorado - Mis pedidos')

@section('content')
    <style>
        .orders-grid { display: grid; gap: 10px; }
        .order-card {
            border: 1px solid #ffd7bd;
            border-radius: 22px;
            padding: 18px;
            background: linear-gradient(180deg, #fffdf9 0%, #fff6ee 100%);
            box-shadow: 0 18px 34px rgba(52, 17, 0, .07);
            color: #25170f;
            line-height: 1.55;
        }
        .order-card,
        .order-card * {
            color: #25170f !important;
            text-shadow: none !important;
        }
        .order-card strong {
            color: #5f3111 !important;
            font-weight: 950;
        }
        .order-card a {
            color: #f25d00 !important;
            font-weight: 900;
        }
        .proof-box {
            margin-top: 8px;
            padding: 10px;
            border: 1px dashed #ffc89d;
            border-radius: 12px;
            background: #fffaf6;
        }
        .proof-status {
            display: inline-block;
            margin-top: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #fff0e4;
            border: 1px solid #ffc89d;
            color: #914406;
            font-size: 12px;
            font-weight: 700;
        }
        .einvoice-box {
            margin-top: 10px;
            padding: 12px 14px;
            border: 1px solid rgba(234, 182, 138, .72);
            border-radius: 18px;
            background: #fffdf9;
            display: grid;
            gap: 8px;
        }
        .einvoice-box strong,
        .einvoice-box span {
            color: #25170f !important;
        }
        .einvoice-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .einvoice-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 12px;
            border-radius: 999px;
            border: 1px solid rgba(234, 182, 138, .82);
            background: rgba(255, 247, 240, .92);
            color: #82471f !important;
            text-decoration: none;
            font-size: 12px;
            font-weight: 900;
        }
        .orders-grid > strong {
            display: block;
            padding: 18px;
            border: 1px solid rgba(234, 182, 138, .72);
            border-radius: 22px;
            background: #fffdf9;
            color: #25170f !important;
        }
        .tracker-grid { display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: end; }
        .timeline-list { list-style: none; margin: 10px 0 0; padding: 0; display: grid; gap: 8px; }
        .timeline-list li { border: 1px solid #f0d7c3; border-radius: 10px; padding: 9px; background: #fff8f2; opacity: .55; }
        @media (max-width: 720px) { .tracker-grid { grid-template-columns: 1fr; } }
        .pref-card { display:grid; gap:8px; margin-bottom:14px; }
        .pref-toggle { display:flex; align-items:flex-start; gap:10px; font-weight:700; color:#6a3a1a; }
        .toast {
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 9999;
            max-width: min(420px, calc(100vw - 32px));
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid rgba(234, 182, 138, .85);
            background: rgba(255, 247, 240, .96);
            color: #2b1608;
            box-shadow: 0 10px 28px rgba(25, 12, 6, .18);
            transform: translateY(10px);
            opacity: 0;
            pointer-events: none;
            transition: opacity .18s ease, transform .18s ease;
            font-weight: 800;
        }
        .toast.show { opacity: 1; transform: translateY(0); }
        .payment-message { margin-top:10px; padding:12px; border-radius:14px; background:#fff; border:1px solid #f0d7c3; }
        .payment-message[data-state="verified"] { background:#ebfff3; border-color:#22a35a; }
        .payment-message[data-state="rejected"], .payment-message[data-state="error"] { background:#fff0ee; border-color:#d94b3d; }
        .order-accordion { border:1px solid #f3b27f; border-radius:18px; overflow:hidden; background:#fffaf5; }
        .order-accordion summary { display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; padding:16px 18px; cursor:pointer; list-style:none; color:#25170f; background:#fff3e7; }
        .order-accordion summary::-webkit-details-marker { display:none; }
        .order-accordion summary::after { content:'\25BC'; color:#d95200; font-size:18px; transition:transform .2s ease; }
        .order-accordion[open] summary::after { transform:rotate(180deg); }
        .order-summary-main { display:grid; gap:3px; }
        .order-summary-meta { color:#784322; font-size:13px; }
        .order-accordion .order-card { border:0; border-top:1px solid #f3c8a7; border-radius:0; box-shadow:none; }
        .order-products { margin:10px 0; padding:10px 14px; border-radius:12px; background:#fff; }
    </style>
    <h1 class="title">Mis pedidos y seguimiento</h1>

    <section class="panel">
        <p style="margin-top:0; font-size:14px; color:#6a3a1a;">
            Aqui siempre veras tus pedidos y codigos de seguimiento, incluso si sales del carrito.
        </p>
        <div class="pref-card">
            <label class="pref-toggle">
                <input id="marketingEmailsEnabled" type="checkbox" style="width:auto; margin-top:3px;">
                <span>Quiero recibir promociones y recordatorios por correo.</span>
            </label>
            <div id="prefMsg" style="font-size:13px; color:#6a3a1a;"></div>
        </div>
        <div id="ordersList" class="orders-grid">Cargando pedidos...</div>
    </section>

    <div id="toast" class="toast" role="status" aria-live="polite"></div>
@endsection

@section('scripts')
<script>
const statusOrder = ['pending', 'confirmed', 'preparing', 'on_the_way', 'delivered'];
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
    error: 'Error',
};
function paymentMessage(status) {
    const messages = {
        verified: ['Pago realizado exitosamente', 'Izipay confirmo correctamente el pago de tu pedido.'],
        pending: ['Pago pendiente de confirmacion', 'Estamos verificando el resultado de la transaccion.'],
        rejected: ['El pago fue rechazado', 'Revisa los datos ingresados o intenta con otro medio de pago.'],
        error: ['No se pudo verificar el pago', 'No se realizo ningun cargo confirmado. Intenta nuevamente.'],
    };
    const item = messages[status] || messages.pending;
    return `<div class="payment-message" data-state="${status}"><strong>${item[0]}</strong><br><span>${item[1]}</span></div>`;
}
const ordersList = document.getElementById('ordersList');
const toastEl = document.getElementById('toast');
const marketingEmailsEnabled = document.getElementById('marketingEmailsEnabled');
const prefMsg = document.getElementById('prefMsg');
let toastTimer = null;
function showToast(message) {
    if (!toastEl) return;
    toastEl.textContent = message;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toastEl.classList.remove('show'), 2600);
}
function loadLastStatuses() {
    try { return JSON.parse(localStorage.getItem('ed_order_statuses') || '{}') || {}; } catch { return {}; }
}
function saveLastStatuses(map) {
    localStorage.setItem('ed_order_statuses', JSON.stringify(map || {}));
}

function getToken() { return localStorage.getItem('ed_token'); }
function statusEs(code) { return STATUS_ES[code] || code || 'n/a'; }
function paymentStatusEs(code) { return PAYMENT_STATUS_ES[code] || code || 'n/a'; }
function needsDigitalProof(method) { return false; }
async function loadPreferences() {
    const token = getToken();
    if (!token) {
        marketingEmailsEnabled.disabled = true;
        prefMsg.textContent = 'Inicia sesion para gestionar tus correos.';
        return;
    }

    try {
        const res = await fetch('/api/v1/profile/preferences', {
            headers: { 'Authorization': `Bearer ${token}` },
        });
        const data = await res.json();
        if (!res.ok) {
            prefMsg.textContent = 'No se pudo cargar tu preferencia de correo.';
            return;
        }

        marketingEmailsEnabled.checked = !!data.marketing_emails_enabled;
        prefMsg.textContent = data.marketing_emails_enabled
            ? 'Recibiras promociones y recordatorios por correo.'
            : 'No recibiras promociones por correo.';
    } catch {
        prefMsg.textContent = 'No se pudo conectar para cargar tus preferencias.';
    }
}

async function savePreferences() {
    const token = getToken();
    if (!token) return;

    prefMsg.textContent = 'Guardando preferencia...';

    try {
        const res = await fetch('/api/v1/profile/preferences', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({
                marketing_emails_enabled: marketingEmailsEnabled.checked,
            }),
        });
        const data = await res.json();
        if (!res.ok) {
            prefMsg.textContent = data.message || 'No se pudo guardar la preferencia.';
            return;
        }

        prefMsg.textContent = marketingEmailsEnabled.checked
            ? 'Listo. Te llegaran promociones y recordatorios por correo.'
            : 'Listo. Dejaste de recibir promociones por correo.';
    } catch {
        prefMsg.textContent = 'No se pudo conectar para guardar la preferencia.';
    }
}

function orderTimelineHtml(status) {
    const normalized=String(status||'').toLowerCase(),current=statusOrder.indexOf(normalized),rows=[...statusOrder,'cancelled'];
    return `<ul class="timeline-list">${rows.map((code,index)=>{const active=normalized==='cancelled'?code==='cancelled':index<=current&&code!=='cancelled';const currentStep=code===normalized;const style=active?`opacity:1;border-color:${currentStep?'#ff6f1f':'#22a35a'};background:${currentStep?'#fff1e5':'#ebfff3'}`:'';return `<li style="${style}">${statusEs(code)}</li>`}).join('')}</ul>`;
}

async function fetchMyOrders() {
    const token = getToken();
    if (!token) {
        ordersList.innerHTML = '<strong>Debes iniciar sesion para ver tus pedidos.</strong>';
        return;
    }

    try {
        const res = await fetch('/api/v1/orders/my', {
            headers: { 'Authorization': `Bearer ${token}` },
        });
        const data = await res.json();
        if (!res.ok) {
            ordersList.innerHTML = '<strong>No se pudo cargar tus pedidos.</strong>';
            return;
        }

        if (!Array.isArray(data) || !data.length) {
            ordersList.innerHTML = '<strong>Aun no tienes pedidos.</strong>';
            return;
        }

        // Notifica cambios de estado (polling) cuando el admin actualiza.
        const prev = loadLastStatuses();
        const next = { ...prev };
        data.forEach(order => {
            const code = (order.tracking_code || '').toString();
            const status = (order.status || '').toString();
            if (!code) return;
            if (prev[code] && prev[code] !== status) {
                showToast(`Tu pedido ${code} ahora esta: ${statusEs(status)}`);
            }
            next[code] = status;
        });
        saveLastStatuses(next);

        ordersList.innerHTML = data.map((order,index) => `
            <details class="order-accordion" ${index===0?'open':''}>
              <summary><span class="order-summary-main"><strong>Pedido ${order.tracking_code}</strong><span class="order-summary-meta">${new Date(order.created_at).toLocaleString()} · ${statusEs(order.status)} · S/ ${Number(order.total_amount).toFixed(2)}</span></span></summary>
              <article class="order-card">
                <div><strong>Codigo:</strong> ${order.tracking_code}</div>
                <div><strong>Fecha/Hora:</strong> ${new Date(order.created_at).toLocaleString()}</div>
                <div><strong>Estado:</strong> ${statusEs(order.status)}</div>
                <div><strong>Total:</strong> S/ ${Number(order.total_amount).toFixed(2)}</div>
                <div><strong>Pago:</strong> ${order.payment_method || 'n/a'} | <strong>Estado pago:</strong> ${paymentStatusEs(order.payment_status)}</div>
                <div><strong>Operacion:</strong> ${order.payment_reference || 'sin codigo'}</div>
                <div class="order-products"><strong>Productos comprados</strong>${Array.isArray(order.items)&&order.items.length?order.items.map(item=>`<div>${item.quantity} × ${item.product_name} · S/ ${Number(item.line_total).toFixed(2)}</div>`).join(''):'<div>Detalle no disponible</div>'}</div>
                ${String(order.payment_method || '').toLowerCase() === 'izipay' ? paymentMessage(String(order.payment_status || 'pending').toLowerCase()) : ''}
                ${needsDigitalProof(order.payment_method) ? `
                <div class="proof-box">
                    <div><strong>Voucher digital</strong></div>
                    <div class="proof-status">
                        ${order.payment_proof_path ? 'Comprobante subido' : 'Falta subir comprobante para validacion'}
                    </div>
                    <div style="margin-top:8px;">
                        <input type="file" data-proof-file="${order.id}" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        <button data-proof-upload="${order.id}" class="btn-soft">Subir comprobante</button>
                    </div>
                </div>` : ''}
                <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:6px;">
                    ${String(order.payment_method || '').toLowerCase() === 'izipay'
                        && ['pending', 'rejected'].includes(String(order.payment_status || '').toLowerCase())
                        && String(order.status || '').toLowerCase() !== 'cancelled'
                        ? `<button data-izipay-checkout="${order.id}" class="btn-soft">Pagar ahora</button>`
                        : ''}
                </div>
                <div><strong>Estado de tu compra</strong>${orderTimelineHtml(order.status)}</div>
              </article>
            </details>
        `).join('');
        ordersList.querySelectorAll('[data-proof-upload]').forEach(btn => {
            btn.addEventListener('click', () => uploadProof(Number(btn.getAttribute('data-proof-upload'))));
        });
        ordersList.querySelectorAll('[data-izipay-checkout]').forEach(btn => {
            btn.addEventListener('click', () => openIzipayCheckout(Number(btn.getAttribute('data-izipay-checkout'))));
        });

    } catch {
        ordersList.innerHTML = '<strong>Error de conexion al cargar pedidos.</strong>';
    }
}

async function uploadProof(orderId) {
    const token = getToken();
    const fileInput = document.querySelector(`[data-proof-file="${orderId}"]`);
    const file = fileInput?.files?.[0];
    if (!file) {
        alert('Selecciona un archivo primero');
        return;
    }

    const formData = new FormData();
    formData.append('proof', file);

    try {
        const res = await fetch(`/api/v1/orders/${orderId}/payment-proof`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData,
        });
        const data = await res.json();
        if (!res.ok) {
            alert(data.message || 'No se pudo subir comprobante');
            return;
        }
        alert('Comprobante subido correctamente');
        fetchMyOrders();
    } catch {
        alert('Error de conexion al subir comprobante');
    }
}

async function openIzipayCheckout(orderId) {
    const token = getToken();
    const button = document.querySelector(`[data-izipay-checkout="${orderId}"]`);
    if (button?.disabled) return;
    if (button) { button.disabled = true; button.textContent = 'Abriendo Izipay...'; }
    try {
        const res = await fetch(`/api/v1/orders/${orderId}/payments/izipay-checkout`, {
            headers: { 'Authorization': `Bearer ${token}` },
        });
        const data = await res.json();
        if (!res.ok) {
            alert(data.message || 'No se pudo iniciar el pago.');
            return;
        }
        const target = data.payment_url;
        if (!target) {
            alert('Izipay aun no esta configurado en el servidor.');
            return;
        }
        window.open(target, '_blank', 'noopener');
    } catch {
        alert('Error de conexion al abrir Izipay.');
    } finally {
        if (button) { button.disabled = false; button.textContent = 'Pagar ahora'; }
    }
}

async function downloadReceipt(orderId) {
    const token = getToken();
    try {
        const res = await fetch(`/api/v1/orders/${orderId}/receipt`, {
            headers: { 'Authorization': `Bearer ${token}` },
        });
        if (!res.ok) {
            alert('No se pudo descargar ticket');
            return;
        }
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `boleta-${orderId}.pdf`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    } catch {
        alert('Error de conexion al descargar ticket');
    }
}

function viewReceipt(orderId) {
    const token = getToken();
    if (!token) {
        alert('Debes iniciar sesion');
        return;
    }
    const url = `/api/v1/orders/${orderId}/receipt-view?token_preview=1`;
    fetch(url, {
        headers: { 'Authorization': `Bearer ${token}` },
    }).then(async (res) => {
        if (!res.ok) {
            alert('No se pudo abrir la boleta');
            return;
        }
        const blob = await res.blob();
        const blobUrl = URL.createObjectURL(blob);
        const win = window.open(blobUrl, '_blank');
        if (!win) {
            alert('Tu navegador bloqueo la ventana emergente');
            URL.revokeObjectURL(blobUrl);
            return;
        }
        setTimeout(() => URL.revokeObjectURL(blobUrl), 60000);
    }).catch(() => alert('Error de conexion al abrir boleta'));
}

marketingEmailsEnabled?.addEventListener('change', savePreferences);
window.addEventListener('ed:order-status-updated', () => {
    fetchMyOrders();
});
fetchMyOrders();
loadPreferences();
setInterval(fetchMyOrders, 45000);
</script>
@endsection
