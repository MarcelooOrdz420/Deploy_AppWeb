<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado de pago</title>
    <style>
        body{font-family:system-ui;background:#fff3e8;color:#25170f;display:grid;place-items:center;min-height:100vh;margin:0;padding:20px}
        main{max-width:520px;background:#fff;padding:28px;border-radius:24px;box-shadow:0 18px 50px rgba(52,17,0,.12);text-align:center}
        .actions{display:grid;gap:10px;margin-top:20px}a,button{display:block;width:100%;padding:14px 18px;border-radius:14px;background:#ff6f1f;color:#fff;font-weight:900;text-decoration:none;border:0;font-size:15px;font-family:inherit;cursor:pointer}.secondary{background:#fff3e8;color:#b84d09;border:1px solid #eab68a}button:disabled{opacity:.6;cursor:default}
    </style>
</head>
<body><main><h1 id="title">Verificando pago</h1><p id="message">{{ $confirmationError ?: 'Estamos consultando la confirmacion autentica enviada por Izipay.' }}</p><div class="actions">@if($isMobileClient)<a href="eldorado:///pedidos">Volver a la app · Ver mis pedidos</a>@else<a class="secondary" href="{{ route('store.orders') }}">Ver mis pedidos</a>@endif<button id="cancelBtn" class="secondary" type="button">Cancelar este pedido</button></div></main>
<script>
const title=document.getElementById('title'),message=document.getElementById('message'),cancelBtn=document.getElementById('cancelBtn');let attempts=0,stopped=false;
const paymentName='tarjeta';async function check(){if(stopped)return;attempts++;try{const response=await fetch(@json($statusUrl),{cache:'no-store'}),data=await response.json(),status=String(data.payment_status||'pending');if(status==='verified'){stopped=true;cancelBtn.style.display='none';localStorage.setItem('ed_cart','[]');window.dispatchEvent(new Event('storage'));title.textContent=`Pago con ${paymentName} confirmado`;message.textContent='Tu compra fue validada correctamente. Regresa a la app para ver el pedido y su comprobante.';return}if(status==='rejected'){title.textContent=`Pago con ${paymentName} rechazado`;message.textContent='No se pudo completar el pago. Puedes cancelar este pedido o volver a intentarlo desde la tienda.';return}}catch{}if(attempts<20)setTimeout(check,3000);else message.textContent=`Estamos verificando tu pago con ${paymentName}. Regresa a la app y revisa Mis pedidos.`}check();
cancelBtn.addEventListener('click',async()=>{if(!confirm('Si cancelas, este pedido no se guardara como compra y tu carrito se vaciara. ¿Deseas continuar?'))return;cancelBtn.disabled=true;cancelBtn.textContent='Cancelando...';try{const response=await fetch(@json($cancelUrl),{method:'POST',headers:{'Accept':'application/json'},cache:'no-store'});if(!response.ok)throw new Error('No se pudo cancelar');stopped=true;localStorage.setItem('ed_cart','[]');window.dispatchEvent(new Event('storage'));cancelBtn.style.display='none';title.textContent='Pedido cancelado';message.textContent='Cancelaste el pago. Tu carrito se vacio; puedes armar un pedido nuevo cuando quieras.'}catch{cancelBtn.disabled=false;cancelBtn.textContent='Cancelar este pedido';alert('No se pudo cancelar el pedido. Puede que el pago ya se haya confirmado; revisa Mis pedidos.')}});
</script></body></html>
