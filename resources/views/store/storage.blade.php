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
            <div class="storage-gcs-result">
                <h2>Archivo subido exitosamente</h2>
                <p>Tu archivo fue subido al bucket <strong>{{ $bucketName }}</strong>.</p>
                <div class="storage-gcs-actions">
                    @if (!empty($uploaded['signed_url']))
                        <a href="{{ $uploaded['signed_url'] }}" target="_blank" rel="noopener noreferrer">Abrir URL firmada</a>
                    @endif
                    @if (!empty($uploaded['public_url']))
                        <a href="{{ $uploaded['public_url'] }}" target="_blank" rel="noopener noreferrer">Abrir en bucket</a>
                    @endif
                </div>
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
        border: 1px solid rgba(255, 215, 0, .22);
        border-radius: 8px;
        background: linear-gradient(180deg, #050403 0%, #080605 50%, #110b08 100%);
        color: #ffffff;
        box-shadow: 0 28px 70px rgba(0, 0, 0, .34);
    }

    .storage-gcs-hero {
        padding: 52px 24px 112px;
        text-align: center;
    }

    .storage-gcs-eyebrow {
        display: inline-flex;
        border: 1px solid rgba(255, 215, 0, .28);
        border-radius: 999px;
        background: rgba(255, 215, 0, .10);
        padding: 6px 14px;
        color: #ffe38a;
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
        background: linear-gradient(90deg, transparent, rgba(255, 215, 0, .28), transparent);
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
        background: #ffffff;
        color: #111111;
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
        background: linear-gradient(135deg, #7c3f18, #4b2a61);
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
        width: min(720px, 100%);
        margin: 44px auto 0;
        border: 1px solid rgba(74, 222, 128, .35);
        border-radius: 8px;
        background: rgba(34, 197, 94, .10);
        padding: 28px;
        text-align: center;
    }

    .storage-gcs-result h2 {
        margin: 0;
        color: #bbf7d0;
        font-size: 22px;
    }

    .storage-gcs-result p {
        color: rgba(255, 248, 226, .88);
    }

    .storage-gcs-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
        margin-top: 22px;
    }

    .storage-gcs-warning {
        margin-top: 18px;
        color: #fde68a;
    }
</style>
@endsection