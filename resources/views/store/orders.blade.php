@extends('store.layout')

@section('title', 'El Dorado - Mis pedidos')

@section('content')
    <style>
        .orders-grid { display: grid; gap: 10px; }
        .order-card {
            border: 1px solid #ffd7bd;
            border-radius: 12px;
            padding: 12px;
            background: linear-gradient(170deg, #fff 0%, #fff8f2 100%);
            box-shadow: 0 8px 20px rgba(255, 111, 31, .08);
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
        .tracker-grid { display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: end; }
        .timeline-list { list-style: none; margin: 10px 0 0; padding: 0; display: grid; gap: 8px; }
        .timeline-list li { border: 1px solid #f0d7c3; border-radius: 10px; padding: 9px; background: #fff8f2; opacity: .55; }
        @media (max-width: 720px) { .tracker-grid { grid-template-columns: 1fr; } }
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
    </style>
    <h1 class="title">Mis pedidos y seguimiento</h1>

    <section class="panel">
        <p style="margin-top:0; font-size:14px; color:#6a3a1a;">
            Aqui siempre veras tus pedidos y codigos de seguimiento, incluso si sales del carrito.
        </p>
        <div id="ordersList" class="orders-grid">Cargando pedidos...</div>
    </section>

    <section class="panel">
        <h3 style="margin-top:0;">Buscar por codigo</h3>
        <div class="tracker-grid">
            <div>
                <label for="trackInput">Codigo de seguimiento</label>
                <input id="trackInput" type="text" placeholder="ED-XXXXXXXX">
            </div>
            <button id="trackBtn" type="button" class="btn-main">
                Buscar
            </button>
        </div>
        <div id="trackMsg" style="font-size:13px; opacity:.8; margin-top:8px;"></div>
        <ul id="timeline" class="timeline-list">
            <li data-status="pending">Pendiente</li>
            <li data-status="confirmed">Confirmado</li>
            <li data-status="preparing">Preparando</li>
            <li data-status="on_the_way">En camino</li>
            <li data-status="delivered">Entregado</li>
            <li data-status="cancelled">Cancelado</li>
        </ul>
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
};
const ordersList = document.getElementById('ordersList');
const trackInput = document.getElementById('trackInput');
const trackBtn = document.getElementById('trackBtn');
const trackMsg = document.getElementById('trackMsg');
const timeline = document.getElementById('timeline');
const toastEl = document.getElementById('toast');
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
function needsDigitalProof(method) { return ['yape', 'plin', 'transfer'].includes(String(method || '').toLowerCase()); }

function paintTimeline(status) {
    const normalized = String(status || '').toLowerCase();
    const currentIdx = statusOrder.indexOf(normalized);
    timeline.querySelectorAll('li').forEach(item => {
        const itemStatus = item.getAttribute('data-status');
        item.style.opacity = '.55';
        item.style.borderColor = '#f0d7c3';
        item.style.background = '#fff8f2';

        if (normalized === 'cancelled') {
            if (itemStatus === 'cancelled') {
                item.style.opacity = '1';
                item.style.borderColor = '#ff6f1f';
                item.style.background = '#fff1e5';
            }
            return;
        }

        const idx = statusOrder.indexOf(itemStatus);
        if (idx === -1) return;
        if (idx < currentIdx) {
            item.style.opacity = '1';
            item.style.borderColor = '#22a35a';
            item.style.background = '#ebfff3';
        }
        if (idx === currentIdx) {
            item.style.opacity = '1';
            item.style.borderColor = '#ff6f1f';
            item.style.background = '#fff1e5';
        }
    });
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

        ordersList.innerHTML = data.map(order => `
            <article class="order-card">
                <div><strong>Codigo:</strong> ${order.tracking_code}</div>
                <div><strong>Fecha/Hora:</strong> ${new Date(order.created_at).toLocaleString()}</div>
                <div><strong>Estado:</strong> ${statusEs(order.status)}</div>
                <div><strong>Total:</strong> S/ ${Number(order.total_amount).toFixed(2)}</div>
                <div><strong>Pago:</strong> ${order.payment_method || 'n/a'} | <strong>Estado pago:</strong> ${paymentStatusEs(order.payment_status)}</div>
                <div><strong>Operacion:</strong> ${order.payment_reference || 'sin codigo'}</div>
                <div><strong>Comprobante:</strong> ${order.payment_proof_path ? `<a href="${order.payment_proof_path}" target="_blank">Ver archivo</a>` : 'no subido'}</div>
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
                    <button data-track="${order.tracking_code}" class="btn-soft">Ver seguimiento</button>
                    <button data-view-receipt="${order.id}" class="btn-soft">Ver boleta</button>
                    <button data-download="${order.id}" class="btn-soft">Descargar boleta</button>
                </div>
            </article>
        `).join('');

        ordersList.querySelectorAll('[data-track]').forEach(btn => {
            btn.addEventListener('click', () => {
                trackInput.value = btn.getAttribute('data-track');
                searchTracking();
            });
        });
        ordersList.querySelectorAll('[data-download]').forEach(btn => {
            btn.addEventListener('click', () => downloadReceipt(Number(btn.getAttribute('data-download'))));
        });
        ordersList.querySelectorAll('[data-view-receipt]').forEach(btn => {
            btn.addEventListener('click', () => viewReceipt(Number(btn.getAttribute('data-view-receipt'))));
        });
        ordersList.querySelectorAll('[data-proof-upload]').forEach(btn => {
            btn.addEventListener('click', () => uploadProof(Number(btn.getAttribute('data-proof-upload'))));
        });

        const last = localStorage.getItem('ed_last_tracking');
        if (last) {
            trackInput.value = last;
            searchTracking();
        }
    } catch {
        ordersList.innerHTML = '<strong>Error de conexion al cargar pedidos.</strong>';
    }
}

async function searchTracking() {
    const code = trackInput.value.trim().toUpperCase();
    if (!code) {
        trackMsg.textContent = 'Ingresa un codigo de seguimiento.';
        return;
    }
    trackMsg.textContent = 'Buscando...';
    try {
        const res = await fetch(`/api/v1/orders/track/${encodeURIComponent(code)}`);
        const data = await res.json();
        if (!res.ok) {
            trackMsg.textContent = data.message || 'Pedido no encontrado.';
            paintTimeline('');
            return;
        }
        trackMsg.textContent = `Estado: ${statusEs(data.status)} | Pago: ${data.payment_method || 'n/a'} | Pago estado: ${paymentStatusEs(data.payment_status)} | Operacion: ${data.payment_reference || 'sin codigo'}`;
        paintTimeline(data.status);
    } catch {
        trackMsg.textContent = 'No se pudo conectar al servidor.';
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

trackBtn.addEventListener('click', searchTracking);
paintTimeline('');
fetchMyOrders();
setInterval(fetchMyOrders, 15000);
</script>
@endsection
