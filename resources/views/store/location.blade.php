@extends('store.layout')

@section('title', 'El Dorado - Ubicacion')

@section('content')
    <style>
        .location-shell { display:grid; gap:18px; }
        .location-grid { display:grid; grid-template-columns: 1fr 1fr; gap:14px; }
        .location-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid rgba(255,122,26,.22);
            background: linear-gradient(180deg, #ffffff 0%, #fff6eb 100%);
        }
        .location-map {
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid rgba(255,122,26,.22);
            min-height: 420px;
            background: linear-gradient(180deg, #fff8ef 0%, #fff2e4 100%);
        }
        .location-map iframe {
            width: 100%;
            height: 100%;
            min-height: 420px;
            border: 0;
            display: block;
        }
        .location-list { display:grid; gap:10px; }
        .location-list strong { color: #16110c; }
        @media (max-width: 900px) {
            .location-grid { grid-template-columns: 1fr; }
        }
    </style>

    <section class="location-shell">
        <article class="panel">
            <p class="eyebrow">Ubicacion y Cobertura</p>
            <h1 class="title">Encuentranos, recoge tu pedido o coordina tu delivery con mas precision.</h1>
            <p class="muted-main">
                La ubicacion ya no es solo una referencia: ahora forma parte del flujo operativo del pedido.
                Si eliges delivery puedes enviar direccion, referencia y, cuando corresponda, ubicacion exacta.
            </p>
        </article>

        <section class="location-grid">
            <article class="location-card">
                <p class="eyebrow">Punto Principal</p>
                <div class="location-list">
                    <div><strong>Direccion:</strong> <span id="locationAddress">Jr. Cuzco, Huancayo, Peru</span></div>
                    <div><strong>Referencia:</strong> <span id="locationReference">Zona comercial cercana a Rock and Pop</span></div>
                    <div><strong>Telefono:</strong> <span id="locationPhone">964900990</span></div>
                    <div><strong>Horario operativo:</strong> <span id="locationHours">Atencion continua hasta las 11:00 PM</span></div>
                    <div><strong>Modalidad:</strong> <span id="locationModes">Atencion en local, recojo y delivery</span></div>
                </div>
                <div style="margin-top:14px;">
                    <a id="locationMapsLink" href="https://maps.google.com/?q=Jr.%20Cuzco%20Huancayo%20Peru" target="_blank" rel="noreferrer" class="btn-main" style="text-decoration:none; display:inline-flex;">
                        Abrir mapa
                    </a>
                </div>
            </article>

            <article class="location-card">
                <p class="eyebrow">Recomendaciones</p>
                <div class="location-list">
                    <div><strong>Para delivery:</strong> <span id="locationDeliveryNotes">Envia una referencia visible como color de puerta, piso o negocio cercano.</span></div>
                    <div><strong>Para recojo:</strong> <span id="locationPickupNotes">Programa hora si buscas evitar espera en hora pico.</span></div>
                    <div><strong>Para pedidos grandes:</strong> agenda con anticipacion para asegurar tiempo de cocina y despacho.</div>
                    <div><strong>Para ubicacion exacta:</strong> usa la geolocalizacion desde el flujo de pedido cuando este disponible.</div>
                </div>
            </article>
        </section>

        <section class="location-grid">
            <article class="location-map">
                <iframe
                    id="locationMapFrame"
                    title="Mapa de Pollos y Parrillas El Dorado"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://maps.google.com/maps?q=Jr.%20Cuzco%20Huancayo%20Peru&t=&z=16&ie=UTF8&iwloc=&output=embed"></iframe>
            </article>

            <article class="location-card">
                <p class="eyebrow">Como usar esta ubicacion</p>
                <div class="location-list">
                    <div><strong>Cliente web:</strong> ahora puedes validar visualmente si el local te queda cerca antes de pedir recojo.</div>
                    <div><strong>Delivery:</strong> el mapa ayuda a reducir errores cuando el cliente complementa su direccion con GPS.</div>
                    <div><strong>Operacion:</strong> el admin gana una referencia mas clara para soporte, llamadas y seguimiento.</div>
                    <div><strong>Siguiente mejora sugerida:</strong> dejar esta direccion configurable desde admin para no depender del codigo.</div>
                </div>
            </article>
        </section>
    </section>

    <script>
    (() => {
        async function loadLocationSettings() {
            try {
                const response = await fetch('/api/v1/settings/public');
                const data = await response.json();
                if (!response.ok || !data?.location) return;

                const location = data.location;
                const setText = (id, value) => {
                    const el = document.getElementById(id);
                    if (el && value) el.textContent = value;
                };

                setText('locationAddress', location.address);
                setText('locationReference', location.reference);
                setText('locationPhone', data.support_phone);
                setText('locationHours', location.business_hours);
                setText('locationModes', location.service_modes);
                setText('locationDeliveryNotes', location.delivery_notes);
                setText('locationPickupNotes', location.pickup_notes);

                const mapsLink = document.getElementById('locationMapsLink');
                if (mapsLink && location.google_maps_url) mapsLink.href = location.google_maps_url;

                const mapFrame = document.getElementById('locationMapFrame');
                if (mapFrame && location.google_maps_embed_url) mapFrame.src = location.google_maps_embed_url;
            } catch {}
        }

        loadLocationSettings();
    })();
    </script>
@endsection
