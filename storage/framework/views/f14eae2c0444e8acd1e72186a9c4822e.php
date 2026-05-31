<?php $__env->startSection('title', 'El Dorado - Ubicacion'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .location-shell { display:grid; gap:18px; }
        .location-grid { display:grid; grid-template-columns: 1fr 1fr; gap:14px; }
        .location-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid rgba(255,122,26,.22);
            background: linear-gradient(180deg, #1a1a1a 0%, #121212 100%);
        }
        .location-list { display:grid; gap:10px; }
        .location-list strong { color: #ff9a3d; }
        @media (max-width: 900px) {
            .location-grid { grid-template-columns: 1fr; }
        }
    </style>

    <section class="location-shell">
        <article class="panel">
            <p class="eyebrow">Ubicación y Cobertura</p>
            <h1 class="title">Encuéntranos, recoge tu pedido o coordina tu delivery con más precisión.</h1>
            <p class="muted-main">
                La ubicación ya no es solo una referencia: ahora forma parte del flujo operativo del pedido.
                Si eliges delivery puedes enviar dirección, referencia y, cuando corresponda, ubicación exacta.
            </p>
        </article>

        <section class="location-grid">
            <article class="location-card">
                <p class="eyebrow">Punto Principal</p>
                <div class="location-list">
                    <div><strong>Dirección:</strong> Jr. Cuzco, Huancayo, Perú</div>
                    <div><strong>Referencia:</strong> zona comercial cercana a Rock and Pop</div>
                    <div><strong>Teléfono:</strong> 964900990</div>
                    <div><strong>Horario operativo:</strong> atención continua hasta las 11:00 PM</div>
                    <div><strong>Modalidad:</strong> atención en local, recojo y delivery</div>
                </div>
                <div style="margin-top:14px;">
                    <a href="https://maps.google.com/?q=-12.0464,-77.0428" target="_blank" rel="noreferrer" class="btn-main" style="text-decoration:none; display:inline-flex;">
                        Abrir mapa
                    </a>
                </div>
            </article>

            <article class="location-card">
                <p class="eyebrow">Recomendaciones</p>
                <div class="location-list">
                    <div><strong>Para delivery:</strong> envía una referencia visible como color de puerta, piso o negocio cercano.</div>
                    <div><strong>Para recojo:</strong> programa hora si buscas evitar espera en hora pico.</div>
                    <div><strong>Para pedidos grandes:</strong> agenda con anticipación para asegurar tiempo de cocina y despacho.</div>
                    <div><strong>Para ubicación exacta:</strong> usa la geolocalización desde el flujo de pedido cuando esté disponible.</div>
                </div>
            </article>
        </section>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('store.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Proyectos\WEB_POLLERIA\laravel-app\resources\views\store\location.blade.php ENDPATH**/ ?>