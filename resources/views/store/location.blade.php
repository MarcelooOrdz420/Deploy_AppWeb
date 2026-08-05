@extends('store.layout')

@section('title', 'El Dorado - Ubicacion')

@section('content')
    <style>
        .location-shell { display:grid; gap:18px; }
        .location-grid { display:grid; grid-template-columns: minmax(0,760px); justify-content:center; gap:14px; }
        .location-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid rgba(255,122,26,.22);
            background: linear-gradient(180deg, #ffffff 0%, #fff6eb 100%);
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
            <h1 class="title">Encuéntranos en nuestro local El Dorado.</h1>
            <p class="muted-main">
                Consulta el nombre y la dirección del local antes de realizar tu pedido o elegir recojo.
            </p>
        </article>

        <section class="location-grid">
            <article class="location-card">
                <p class="eyebrow">Nuestro local</p>
                <div class="location-list">
                    <div><strong>Nombre:</strong> <span id="locationName">Local principal El Dorado</span></div>
                    <div><strong>Direccion:</strong> <span id="locationAddress">Jr. Cuzco, Huancayo, Peru</span></div>
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

                setText('locationName', location.location_name);
                setText('locationAddress', location.address);
            } catch {}
        }

        loadLocationSettings();
    })();
    </script>
@endsection
