<?php $__env->startSection('title', 'El Dorado - Quienes Somos'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .about-shell { display:grid; gap:18px; }
        .about-hero, .about-grid, .about-timeline { display:grid; gap:14px; }
        .about-hero { grid-template-columns: 1.1fr .9fr; }
        .about-grid { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        .about-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid rgba(255,122,26,.22);
            background: linear-gradient(180deg, #1a1a1a 0%, #121212 100%);
        }
        .about-accent {
            background: linear-gradient(135deg, #ff7a1a 0%, #ff9a3d 100%);
            color: var(--accent-ink);
        }
        .about-metrics { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px; }
        .metric strong { display:block; font-size: 34px; line-height:1; margin-bottom:6px; }
        @media (max-width: 900px) {
            .about-hero, .about-metrics { grid-template-columns: 1fr; }
        }
    </style>

    <section class="about-shell">
        <section class="about-hero">
            <article class="panel">
                <p class="eyebrow">Nuestra Historia</p>
                <h1 class="title">Una polleria pensada para vender bien y atender mejor.</h1>
                <p class="muted-main">
                    Pollos y Parrillas "El Dorado" nace con una idea clara: llevar el sabor clásico del pollo a la brasa peruano
                    a una operación más ordenada, más rápida y más confiable. No solo buscamos servir rico, también
                    buscamos que cada pedido tenga seguimiento, consistencia y una experiencia profesional desde la cocina hasta el delivery.
                </p>
            </article>
            <article class="about-card about-accent">
                <strong style="font-size:26px;">Promesa de marca</strong>
                <p style="margin:10px 0 0; line-height:1.7;">
                    Brasa intensa, tiempos controlados, entrega clara y una operación digital que acompaña el crecimiento del negocio sin improvisaciones.
                </p>
            </article>
        </section>

        <section class="about-metrics">
            <article class="about-card metric">
                <strong>+3</strong>
                <span class="muted-main">frentes de operación integrados: ventas, pedidos y control administrativo.</span>
            </article>
            <article class="about-card metric">
                <strong>11 PM</strong>
                <span class="muted-main">como hora de corte operativa para programar cocina y despacho con mayor precisión.</span>
            </article>
            <article class="about-card metric">
                <strong>100%</strong>
                <span class="muted-main">orientado a una experiencia simple para el cliente y control real para el administrador.</span>
            </article>
        </section>

        <section class="about-grid">
            <article class="about-card">
                <p class="eyebrow">Vision</p>
                <h3 class="section-title">Convertir la polleria en un sistema serio de atención.</h3>
                <p class="muted-main">La meta es que el negocio pueda crecer sin perder control del stock, del estado de pedidos ni de la calidad del servicio.</p>
            </article>
            <article class="about-card">
                <p class="eyebrow">Operacion</p>
                <h3 class="section-title">Cocina, despacho y venta en un mismo flujo.</h3>
                <p class="muted-main">El sistema prioriza visibilidad: quién pidió, cómo pagó, qué se vendió, cuándo se programó y qué stock impactó.</p>
            </article>
            <article class="about-card">
                <p class="eyebrow">Cliente</p>
                <h3 class="section-title">Comprar sin fricción.</h3>
                <p class="muted-main">Buscamos que el cliente encuentre rápido su producto, entienda su pedido y se sienta seguro al pagar o hacer seguimiento.</p>
            </article>
        </section>

        <section class="about-grid">
            <article class="about-card">
                <p class="eyebrow">Lo que cuidamos</p>
                <p class="muted-main">Punto de cocción, tiempos de salida, control del voucher, claridad del pedido y trazabilidad operativa.</p>
            </article>
            <article class="about-card">
                <p class="eyebrow">Lo que mejoramos</p>
                <p class="muted-main">Reducción de errores manuales, mejor lectura de ventas, orden administrativo y una imagen más profesional del negocio.</p>
            </article>
            <article class="about-card">
                <p class="eyebrow">Lo que viene</p>
                <p class="muted-main">Mayor automatización comercial, mejores promociones, control de reservas y crecimiento multicanal sin perder visibilidad.</p>
            </article>
        </section>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('store.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Proyectos\WEB_POLLERIA\laravel-app\resources\views/store/about.blade.php ENDPATH**/ ?>