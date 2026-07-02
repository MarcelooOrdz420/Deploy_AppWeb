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
            font-family: Arial, sans-serif;
            background: #fff8f2;
            color: #271408;
            display: grid;
            place-items: center;
            padding: 18px;
        }
        main {
            width: min(520px, 100%);
            background: #fff;
            border: 1px solid #ffd4b1;
            border-radius: 14px;
            box-shadow: 0 18px 42px rgba(70, 28, 4, .14);
            padding: 18px;
        }
        h1 { margin: 0 0 8px; font-size: 22px; }
        .meta { margin: 0 0 16px; color: #6b4a34; line-height: 1.45; }
        .actions { margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap; }
        a { color: #b84d09; font-weight: 700; }
    </style>
</head>
<body>
    <main>
        <h1>Pago seguro con Izipay</h1>
        <p class="meta">
            Pedido {{ $order->tracking_code }}<br>
            Metodo elegido: {{ $paymentLabel }}<br>
            Total: S/ {{ number_format((float) $order->total_amount, 2) }}
        </p>

        <div class="kr-embedded" kr-form-token="{{ $formToken }}"></div>

        <div class="actions">
            <a href="{{ route('store.orders') }}">Volver a mis pedidos</a>
        </div>
    </main>

    <script
        src="{{ $jsUrl }}"
        kr-public-key="{{ $publicKey }}"
        kr-post-url-success="{{ route('store.orders') }}"
        kr-language="es-ES"></script>
</body>
</html>
