<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado de pago</title>
    <style>
        body{font-family:system-ui;background:#fff3e8;color:#25170f;display:grid;place-items:center;min-height:100vh;margin:0;padding:20px}
        main{max-width:520px;background:#fff;padding:28px;border-radius:24px;box-shadow:0 18px 50px rgba(52,17,0,.12);text-align:center}
        .actions{display:grid;gap:10px;margin-top:20px}a{display:block;padding:14px 18px;border-radius:14px;background:#ff6f1f;color:#fff;font-weight:900;text-decoration:none}.secondary{background:#fff3e8;color:#b84d09;border:1px solid #eab68a}
    </style>
</head>
<body><main><h1 id="title">Verificando pago</h1><p id="message">{{ $confirmationError ?: 'Estamos consultando la confirmacion autentica enviada por Izipay.' }}</p><div class="actions">@if($isMobileClient)<a href="eldorado:///pedidos">Volver a la app · Ver mis pedidos</a>@else<a class="secondary" href="{{ route('store.orders') }}">Ver mis pedidos</a>@endif</div></main>
<script>
const title=document.getElementById('title'),message=document.getElementById('message');let attempts=0;
const paymentName=@json($isYape ? 'Yape' : 'tarjeta');async function check(){attempts++;try{const response=await fetch(@json($statusUrl),{cache:'no-store'}),data=await response.json(),status=String(data.payment_status||'pending');if(status==='verified'){localStorage.setItem('ed_cart','[]');window.dispatchEvent(new Event('storage'));title.textContent=`Pago con ${paymentName} confirmado`;message.textContent='Tu compra fue validada correctamente. Regresa a la app para ver el pedido y su comprobante.';return}if(status==='rejected'){title.textContent=`Pago con ${paymentName} rechazado`;message.textContent='No se pudo completar el pago. Puedes regresar a la app para reintentar o elegir otro método.';return}}catch{}if(attempts<20)setTimeout(check,3000);else message.textContent=`Estamos verificando tu pago con ${paymentName}. Regresa a la app y revisa Mis pedidos.`}check();
</script></body></html>
