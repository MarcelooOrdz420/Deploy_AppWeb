<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado de pago</title>
    <style>
        body{font-family:system-ui;background:#fff3e8;color:#25170f;display:grid;place-items:center;min-height:100vh;margin:0;padding:20px}
        main{max-width:520px;background:#fff;padding:28px;border-radius:24px;box-shadow:0 18px 50px rgba(52,17,0,.12);text-align:center}
        .actions{display:grid;gap:10px;margin-top:20px}a,button{display:block;width:100%;padding:14px 18px;border-radius:14px;background:#ff6f1f;color:#fff;font-weight:900;text-decoration:none;border:0;font-size:15px;font-family:inherit;cursor:pointer}.secondary{background:#fff3e8;color:#b84d09;border:1px solid #eab68a}button:disabled{opacity:.6;cursor:default}
        .modal-overlay{display:none;position:fixed;inset:0;z-index:999;align-items:center;justify-content:center;padding:18px;background:rgba(24,15,8,.55)}
        .modal-card{width:min(92vw,380px);background:#fffdf9;border:1.5px solid #ffb37a;border-radius:26px;box-shadow:0 30px 60px rgba(255,111,31,.28);padding:26px 24px;text-align:center}
        .modal-icon{width:44px;height:44px;border-radius:14px;background:#ffe4d2;color:#ff6f1f;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900;margin:0 auto 14px}
        .modal-title{display:block;font-size:18px;color:#25170f;margin-bottom:8px}
        .modal-message{margin:0 0 18px;color:#68432e;font-size:14.5px;line-height:1.5}
        .modal-actions{display:flex;gap:10px}
        .modal-actions button{margin:0}
    </style>
</head>
<body><main><h1 id="title">Verificando pago</h1><p id="message">{{ $confirmationError ?: 'Estamos consultando la confirmacion autentica enviada por Izipay.' }}</p><div class="actions">@if($isMobileClient)<a href="eldorado:///pedidos">Volver a la app · Ver mis pedidos</a>@else<a class="secondary" href="{{ route('store.orders') }}">Ver mis pedidos</a>@endif<button id="cancelBtn" class="secondary" type="button">Cancelar este pedido</button></div></main>

<div id="confirmOverlay" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-icon">!</div>
        <strong id="confirmTitle" class="modal-title">Cancelar pago</strong>
        <p id="confirmMessage" class="modal-message"></p>
        <div class="modal-actions">
            <button id="confirmCancelBtn" type="button" class="secondary">Seguir esperando</button>
            <button id="confirmOkBtn" type="button">Si, cancelar</button>
        </div>
    </div>
</div>

<div id="alertOverlay" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-icon">!</div>
        <strong id="alertTitle" class="modal-title">Aviso</strong>
        <p id="alertMessage" class="modal-message"></p>
        <button id="alertOkBtn" type="button">Entendido</button>
    </div>
</div>

<script>
function showConfirm(message){
    return new Promise((resolve)=>{
        const overlay=document.getElementById('confirmOverlay');
        document.getElementById('confirmMessage').textContent=message;
        overlay.style.display='flex';
        const okBtn=document.getElementById('confirmOkBtn'),cancelBtn=document.getElementById('confirmCancelBtn');
        const cleanup=(result)=>{overlay.style.display='none';okBtn.removeEventListener('click',onOk);cancelBtn.removeEventListener('click',onCancel);resolve(result)};
        const onOk=()=>cleanup(true),onCancel=()=>cleanup(false);
        okBtn.addEventListener('click',onOk);cancelBtn.addEventListener('click',onCancel);
    });
}
function showAlert(message){
    const overlay=document.getElementById('alertOverlay');
    document.getElementById('alertMessage').textContent=message;
    overlay.style.display='flex';
}
document.getElementById('alertOkBtn')?.addEventListener('click',()=>{document.getElementById('alertOverlay').style.display='none'});

const title=document.getElementById('title'),message=document.getElementById('message'),cancelBtn=document.getElementById('cancelBtn');let attempts=0,stopped=false;
const paymentName='tarjeta';async function check(){if(stopped)return;attempts++;try{const response=await fetch(@json($statusUrl),{cache:'no-store'}),data=await response.json(),status=String(data.payment_status||'pending');if(status==='verified'){stopped=true;cancelBtn.style.display='none';localStorage.setItem('ed_cart','[]');window.dispatchEvent(new Event('storage'));title.textContent=`Pago con ${paymentName} confirmado`;message.textContent='Tu compra fue validada correctamente. Regresa a la app para ver el pedido y su comprobante.';return}if(status==='rejected'){title.textContent=`Pago con ${paymentName} rechazado`;message.textContent='No se pudo completar el pago. Puedes cancelar este pedido o volver a intentarlo desde la tienda.';return}}catch{}if(attempts<20)setTimeout(check,3000);else message.textContent=`Estamos verificando tu pago con ${paymentName}. Regresa a la app y revisa Mis pedidos.`}check();
cancelBtn.addEventListener('click',async()=>{if(!(await showConfirm('Si cancelas, este pedido no se guardara como compra y tu carrito se vaciara. ¿Deseas continuar?')))return;cancelBtn.disabled=true;cancelBtn.textContent='Cancelando...';try{const response=await fetch(@json($cancelUrl),{method:'POST',headers:{'Accept':'application/json'},cache:'no-store'});if(!response.ok)throw new Error('No se pudo cancelar');stopped=true;localStorage.setItem('ed_cart','[]');window.dispatchEvent(new Event('storage'));cancelBtn.style.display='none';title.textContent='Pedido cancelado';message.textContent='Cancelaste el pago. Tu carrito se vacio; puedes armar un pedido nuevo cuando quieras.'}catch{cancelBtn.disabled=false;cancelBtn.textContent='Cancelar este pedido';showAlert('No se pudo cancelar el pedido. Puede que el pago ya se haya confirmado; revisa Mis pedidos.')}});
</script></body></html>
