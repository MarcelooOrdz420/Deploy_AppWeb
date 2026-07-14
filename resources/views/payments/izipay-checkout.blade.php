<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago Izipay - {{ $order->tracking_code }}</title>
    <link rel="stylesheet" href="{{ $cssUrl }}">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255, 157, 90, .22), transparent 28%),
                radial-gradient(circle at top right, rgba(255, 111, 31, .16), transparent 26%),
                linear-gradient(180deg, #fffaf6 0%, #fff1e5 44%, #ffead8 100%);
            color: #25170f;
            display: grid;
            place-items: center;
            padding: 18px;
        }
        main {
            width: min(520px, 100%);
            background: linear-gradient(180deg, rgba(255,255,255,.94), rgba(255,246,238,.96));
            border: 1px solid rgba(234, 182, 138, .78);
            border-radius: 28px;
            box-shadow: 0 26px 60px rgba(52, 17, 0, .13);
            padding: 24px;
        }
        .brand-mark {
            width: 64px;
            height: 64px;
            margin: 0 auto 14px;
            border-radius: 18px;
            border: 1px solid rgba(234, 182, 138, .9);
            background: #fffdf9;
            padding: 8px;
            box-shadow: 0 12px 24px rgba(255, 111, 31, .14);
        }
        .brand-mark img { width: 100%; height: 100%; object-fit: contain; }
        h1 { margin: 0 0 8px; font-size: 24px; color: #25170f; text-align: center; }
        .meta { margin: 0 0 18px; color: #68432e; line-height: 1.45; text-align: center; font-weight: 800; }
        .kr-embedded {
            overflow: hidden;
            border-radius: 22px;
            background: #fffdf9;
        }
        .actions { margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap; }
        a { color: #b84d09; font-weight: 700; }
    </style>
</head>
<body>
    <main>
        <div class="brand-mark">
            <img src="/images/ico-pollo.jpg" alt="El Dorado">
        </div>
        <h1>Pago seguro con Izipay</h1>
        <p class="meta">
            Pedido {{ $order->tracking_code }}<br>
            Total: S/ {{ number_format((float) $order->total_amount, 2) }}
        </p>

        <div class="kr-smart-form" kr-form-token="{{ $formToken }}"></div>
        <div class="kr-form-error" aria-live="polite"></div>

        <div class="actions">
            <a href="{{ route('store.orders') }}">Volver a mis pedidos</a>
        </div>
    </main>

    <script
        src="{{ $jsUrl }}"
        kr-public-key="{{ $publicKey }}"
        kr-post-url-success="{{ $resultUrl }}"
        kr-post-url-refused="{{ $resultUrl }}"
        kr-language="es-ES"></script>
</body>
</html>
