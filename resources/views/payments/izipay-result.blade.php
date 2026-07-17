<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado de pago</title>
    <style>
        body{font-family:system-ui;background:#fff3e8;color:#25170f;display:grid;place-items:center;min-height:100vh;margin:0;padding:20px}
        main{max-width:520px;background:#fff;padding:28px;border-radius:24px;box-shadow:0 18px 50px rgba(52,17,0,.12);text-align:center}
        a{display:inline-block;margin-top:18px;color:#b84d09;font-weight:800}
    </style>
</head>
<body><main><h1 id="title">Verificando pago</h1><p id="message">{{ $confirmationError ?: 'Estamos consultando la confirmacion autentica enviada por Izipay.' }}</p><a href="{{ route('store.orders') }}">Volver a Mis pedidos</a></main>
<script>
const title=document.getElementById('title'),message=document.getElementById('message');let attempts=0;
async function check(){attempts++;try{const response=await fetch(@json($statusUrl),{cache:'no-store'}),data=await response.json(),status=String(data.payment_status||'pending');if(status==='verified'){title.textContent='Pago realizado exitosamente';message.textContent='Izipay confirmo correctamente el pago de tu pedido.';return}if(status==='rejected'){title.textContent='Pago rechazado';message.textContent='Revisa los datos ingresados o intenta nuevamente.';return}}catch{}if(attempts<20)setTimeout(check,3000);else message.textContent='El pago sigue pendiente de confirmacion. Puedes revisarlo en Mis pedidos.'}check();
</script></body></html>
