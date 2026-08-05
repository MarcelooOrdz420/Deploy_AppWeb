@extends('store.layout')
@section('title', 'El Dorado - Seguimiento por código')
@section('content')
<style>
    .tracking-page{display:grid;gap:16px}.tracking-search{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end}.tracking-result{display:none;padding:18px;border-radius:18px;background:#fff8f1;color:#25170f}.tracking-steps{list-style:none;padding:0;display:grid;gap:8px}.tracking-steps li{padding:11px;border:1px solid #edcbb1;border-radius:12px;opacity:.55;background:#fff}.tracking-steps li.done{opacity:1;border-color:#22a35a;background:#ebfff3}.tracking-steps li.current{opacity:1;border-color:#ff6f1f;background:#fff1e5}@media(max-width:650px){.tracking-search{grid-template-columns:1fr}}
</style>
<section class="panel tracking-page">
    <div><p class="eyebrow">Consulta independiente</p><h1 class="title">Seguimiento por código</h1><p class="muted-main">Si tienes una cuenta, tus compras aparecen automáticamente en Mis pedidos. Usa esta pantalla solamente para consultar un código concreto.</p></div>
    <div class="tracking-search"><label>Código de seguimiento<input id="trackingCode" placeholder="ED-XXXXXXXX" maxlength="20"></label><button id="trackingSearch" class="btn-main" type="button">Buscar pedido</button></div>
    <div id="trackingMessage"></div><div id="trackingResult" class="tracking-result"></div>
</section>
@endsection
@section('scripts')
<script>
(() => {
 const labels={pending:'Pendiente',confirmed:'Confirmado',preparing:'Preparando',on_the_way:'En camino',delivered:'Entregado',cancelled:'Cancelado'},steps=['pending','confirmed','preparing','on_the_way','delivered'];
 const input=document.getElementById('trackingCode'),message=document.getElementById('trackingMessage'),result=document.getElementById('trackingResult');
 async function search(){const code=input.value.trim().toUpperCase();if(!code){message.textContent='Ingresa un código.';return}message.textContent='Buscando...';result.style.display='none';try{const res=await fetch(`/api/v1/orders/track/${encodeURIComponent(code)}`),data=await res.json();if(!res.ok){message.textContent=data.message||'Pedido no encontrado.';return}message.textContent='';const current=steps.indexOf(data.status);result.innerHTML=`<h2>Pedido ${data.tracking_code}</h2><p><strong>Estado actual:</strong> ${labels[data.status]||data.status}</p><ul class="tracking-steps">${[...steps,'cancelled'].map((step,index)=>`<li class="${step===data.status?'current':(data.status!=='cancelled'&&index<current?'done':'')}">${labels[step]}</li>`).join('')}</ul>`;result.style.display='block'}catch{message.textContent='No se pudo conectar con el servidor.'}}
 document.getElementById('trackingSearch').addEventListener('click',search);input.addEventListener('keydown',e=>{if(e.key==='Enter')search()});
})();
</script>
@endsection
