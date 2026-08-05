@extends('store.layout')

@section('title', $offer->title.' - El Dorado')

@section('content')
<style>
    .promo-detail { display:grid; grid-template-columns:minmax(0,1fr) minmax(320px,.8fr); overflow:hidden; padding:0!important; background:#fff!important; color:#25170f!important; }
    .promo-detail-media { min-height:480px; background:#ffe3cc; }
    .promo-detail-media img { width:100%; height:100%; min-height:480px; object-fit:cover; display:block; }
    .promo-detail-copy { display:grid; align-content:center; gap:16px; padding:clamp(24px,5vw,60px); }
    .promo-detail-copy h1 { margin:0; color:#25170f!important; font-size:clamp(34px,5vw,62px); line-height:.95; }
    .promo-detail-copy p { margin:0; color:#68432e!important; line-height:1.6; }
    .promo-prices { display:flex; align-items:end; gap:14px; flex-wrap:wrap; }
    .promo-old { color:#826b5f; text-decoration:line-through; font-size:20px; }
    .promo-new { color:#c94700; font-size:38px; font-weight:950; }
    .promo-badge { padding:8px 12px; border-radius:999px; background:#74120d; color:#fff; font-weight:900; }
    .promo-detail-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .promo-detail-actions a,.promo-detail-actions button { min-height:48px; padding:12px 18px; border-radius:999px; border:1px solid #d95c13; background:#fff; color:#6b2a0b; font-weight:900; text-decoration:none; cursor:pointer; }
    .promo-detail-actions .primary { background:#ff7417; color:#fff; }
    #promoDetailMessage { color:#8d3509; font-weight:800; }
    @media(max-width:760px){.promo-detail{grid-template-columns:1fr}.promo-detail-media,.promo-detail-media img{min-height:280px;max-height:390px}.promo-detail-copy{padding:24px 18px}}
</style>
<section class="panel promo-detail">
    <div class="promo-detail-media"><img src="{{ $offer->image_url ?: ($offer->product->image_url ?: '/images/products/default.svg') }}" onerror="this.onerror=null;this.src='/images/products/default.svg'" alt="{{ $offer->title }}"></div>
    <div class="promo-detail-copy">
        <span class="promo-badge">-{{ number_format((float) $offer->discount_percent, 0) }}% PROMOCIÓN</span>
        <h1>{{ $offer->title }}</h1>
        <p><strong>{{ $offer->product->name }}</strong></p>
        <p>{{ $offer->body ?: $offer->message }}</p>
        <div class="promo-prices"><span class="promo-old">Antes S/ {{ number_format((float) $offer->original_price, 2) }}</span><span class="promo-new">S/ {{ number_format((float) $offer->promo_price, 2) }}</span></div>
        <div class="promo-detail-actions">
            <button id="promoBuyBtn" class="primary" type="button">Comprar con descuento</button>
            <a href="{{ route('store.products') }}">Seguir viendo productos</a>
        </div>
        <div id="promoDetailMessage"></div>
    </div>
</section>
@endsection

@section('scripts')
<script>
(() => {
    const offer = @json($offer);
    const product = @json($offer->product);
    document.getElementById('promoBuyBtn')?.addEventListener('click', () => {
        if (!localStorage.getItem('ed_token')) {
            window.location.href = `/login?next=${encodeURIComponent(window.location.pathname)}`;
            return;
        }
        const cart = JSON.parse(localStorage.getItem('ed_cart') || '[]');
        const existing = cart.find(item => Number(item.id) === Number(product.id));
        if (existing) Object.assign(existing, { qty:Number(existing.qty || 0)+1, price:Number(offer.promo_price), original_price:Number(offer.original_price), promo_id:Number(offer.id), promo_title:offer.title });
        else cart.push({ ...product, qty:1, price:Number(offer.promo_price), original_price:Number(offer.original_price), promo_id:Number(offer.id), promo_title:offer.title });
        localStorage.setItem('ed_cart', JSON.stringify(cart));
        window.dispatchEvent(new Event('storage'));
        window.location.href = '/carrito';
    });
})();
</script>
@endsection
