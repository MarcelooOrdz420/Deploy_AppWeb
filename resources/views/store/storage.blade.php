@extends('store.layout')

@section('title', 'Storage | Pollos y Parrillas El Dorado')

@section('content')
@php($uploaded = session('gcs_uploaded'))

<section class="storage-gcs-panel">
    <div class="storage-gcs-hero">
        <span class="storage-gcs-eyebrow">Google Cloud Storage</span>
        <h1>Almacenamiento de Archivos (GCS)</h1>
        <p>Sube tus archivos de manera segura usando Google Cloud Storage.</p>
    </div>

    <div class="storage-gcs-divider"></div>

    <div class="storage-gcs-form-wrap">
        <form action="{{ route('store.storage.upload') }}" method="POST" enctype="multipart/form-data" class="storage-gcs-form">
            @csrf

            <label for="archivo">Subir un archivo</label>
            <div class="storage-gcs-input-wrap">
                <input id="archivo" name="archivo" type="file" required>
            </div>
            @error('archivo')<p class="storage-gcs-error">{{ $message }}</p>@enderror

            <button type="submit">Subir archivo</button>
        </form>

        @if ($uploaded)
            @php($viewUrl = $uploaded['download_url'] ?? $uploaded['signed_url'] ?? $uploaded['public_url'] ?? null)
            <div class="storage-gcs-result">
                <h2>Archivo subido exitosamente</h2>
                <p>Tu archivo ha sido subido a Google Cloud Storage. Puedes descargarlo usando este enlace firmado (valido por 1 hora):</p>
                @if ($viewUrl)
                    <div class="storage-gcs-actions">
                        <a href="{{ $viewUrl }}" download>Ver archivo <span aria-hidden="true">&darr;</span></a>
                    </div>
                @endif
                @if (empty($uploaded['signed_url']) && !empty($uploaded['signed_url_error']))
                    <p class="storage-gcs-warning">{{ $uploaded['signed_url_error'] }}</p>
                @endif
            </div>
        @endif
    </div>
</section>

<style>
    .storage-gcs-panel {
        min-height: 680px;
        overflow: hidden;
        border: 1px solid rgba(255, 138, 24, .38);
        border-radius: 8px;
        background: radial-gradient(circle at 50% 0%, rgba(255, 138, 24, .12), transparent 34%), linear-gradient(180deg, #070504 0%, #0d0805 52%, #140b05 100%);
        color: #ffffff;
        box-shadow: 0 28px 70px rgba(0, 0, 0, .34), inset 0 0 0 1px rgba(255, 138, 24, .08);
    }

    .storage-gcs-hero {
        padding: 52px 24px 112px;
        text-align: center;
    }

    .storage-gcs-eyebrow {
        display: inline-flex;
        border: 1px solid rgba(255, 138, 24, .45);
        border-radius: 999px;
        background: rgba(255, 138, 24, .13);
        padding: 6px 14px;
        color: #ffca8a;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .22em;
        text-transform: uppercase;
    }

    .storage-gcs-hero h1 {
        margin: 18px auto 0;
        max-width: 900px;
        color: #ffffff;
        font-size: clamp(32px, 5vw, 52px);
        line-height: 1.05;
    }

    .storage-gcs-hero p {
        margin: 28px auto 0;
        max-width: 720px;
        color: rgba(255, 248, 226, .9);
        font-size: 18px;
        line-height: 1.7;
    }

    .storage-gcs-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 138, 24, .34), transparent);
    }

    .storage-gcs-form-wrap {
        padding: 92px 24px 100px;
    }

    .storage-gcs-form {
        width: min(520px, 100%);
        margin: 0 auto;
        text-align: center;
    }

    .storage-gcs-form label {
        display: block;
        color: #ffffff;
        font-size: 20px;
        font-weight: 900;
    }

    .storage-gcs-input-wrap {
        margin-top: 20px;
        border: 1px solid rgba(255, 255, 255, .82);
        border-radius: 8px;
        background: rgba(0, 0, 0, .35);
        padding: 10px;
    }

    .storage-gcs-input-wrap input {
        width: 100%;
        color: #ffffff;
        font-size: 14px;
    }

    .storage-gcs-input-wrap input::file-selector-button {
        margin-right: 12px;
        border: 0;
        border-radius: 4px;
        background: #fffaf4;
        color: #21140d;
        padding: 8px 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .storage-gcs-form button,
    .storage-gcs-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        border: 0;
        border-radius: 999px;
        padding: 0 24px;
        background: linear-gradient(135deg, #ff8a18, #ff6f1f);
        color: #ffffff;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        text-transform: uppercase;
        cursor: pointer;
        transition: transform .2s ease, filter .2s ease;
    }

    .storage-gcs-form button {
        width: 100%;
        margin-top: 18px;
    }

    .storage-gcs-form button:hover,
    .storage-gcs-actions a:hover {
        transform: translateY(-1px);
        filter: brightness(1.08);
    }

    .storage-gcs-error {
        margin: 12px 0 0;
        color: #fecaca;
        font-weight: 800;
    }

    .storage-gcs-result {
        width: min(980px, 100%);
        margin: 96px auto 0;
        border: 2px solid #22c55e;
        border-radius: 8px;
        background: rgba(34, 197, 94, .10);
        padding: 38px 28px;
        text-align: center;
    }

    .storage-gcs-result h2 {
        margin: 0;
        color: #22e06f;
        font-size: 20px;
        font-weight: 900;
    }

    .storage-gcs-result p {
        margin: 10px auto 0;
        max-width: 820px;
        color: #ffffff;
        font-size: 16px;
        line-height: 1.55;
    }

    .storage-gcs-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
        margin-top: 20px;
    }

    .storage-gcs-result .storage-gcs-actions a {
        min-width: 164px;
        min-height: 46px;
        background: #fffaf4;
        color: #21140d;
        box-shadow: none;
    }

    .storage-gcs-result .storage-gcs-actions a:hover {
        background: #ffffff;
        filter: none;
    }

    .storage-gcs-warning {
        margin-top: 18px;
        color: #fde68a;
    }
</style>
@endsection