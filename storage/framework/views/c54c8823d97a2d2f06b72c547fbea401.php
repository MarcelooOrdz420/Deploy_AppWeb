<?php $__env->startSection('title', 'El Dorado - Expertos'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .experts-shell { display:grid; gap:18px; }
        .experts-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:14px; }
        .expert-card {
            border-radius: 22px;
            padding: 18px;
            border: 1px solid rgba(255,122,26,.22);
            background: linear-gradient(180deg, #1a1a1a 0%, #121212 100%);
            position: relative;
            overflow: hidden;
        }
        .expert-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255,122,26,.18), transparent 40%);
            pointer-events: none;
        }
        .expert-card > * { position: relative; z-index: 1; }
    </style>

    <section class="experts-shell">
        <article class="panel">
            <p class="eyebrow">Especialidades</p>
            <h1 class="title">Más que vender pollo: operamos una experiencia completa.</h1>
            <p class="muted-main">
                Estos son los frentes donde buscamos diferenciarnos: sabor, velocidad, control y una experiencia digital clara para el cliente final.
            </p>
        </article>

        <section class="experts-grid">
            <article class="expert-card">
                <p class="eyebrow">Brasa</p>
                <h3 class="section-title">Cocción uniforme y punto consistente</h3>
                <p class="muted-main">La prioridad es que cada porción salga con textura, color y jugosidad alineados al estándar de la casa.</p>
            </article>
            <article class="expert-card">
                <p class="eyebrow">Despacho</p>
                <h3 class="section-title">Flujo rápido en hora pico</h3>
                <p class="muted-main">Se trabaja pensando en velocidad operativa para soportar mayor demanda sin perder visibilidad del pedido.</p>
            </article>
            <article class="expert-card">
                <p class="eyebrow">Pedidos Online</p>
                <h3 class="section-title">Seguimiento, pago y control comercial</h3>
                <p class="muted-main">No es solo un catálogo: integra compra, estados, validación de pago, comprobantes y reportes administrativos.</p>
            </article>
            <article class="expert-card">
                <p class="eyebrow">Atención</p>
                <h3 class="section-title">Combos, reservas y volumen</h3>
                <p class="muted-main">Ideal para clientes frecuentes, familias, recojo programado y pedidos con una lógica más profesional.</p>
            </article>
        </section>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('store.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Proyectos\WEB_POLLERIA\laravel-app\resources\views\store\experts.blade.php ENDPATH**/ ?>