<?php

namespace App\Services\Chatbot;

use App\Models\Product;
use Illuminate\Support\Str;

class LocalResponder
{
    public function reply(string $message): ?string
    {
        $normalized = $this->normalize($message);

        if ($this->isSensitiveRequest($normalized)) {
            return 'No puedo mostrar datos internos, administrativos, credenciales ni informacion privada de clientes. Puedo ayudarte con productos publicos, precios disponibles, pagos, delivery, horarios, ubicacion o seguimiento de tu propio pedido.';
        }

        if ($this->matchesAny($normalized, ['ubicacion', 'direccion', 'donde', 'mapa', 'como llegar', 'google maps'])) {
            return $this->knowledgeSection('Ubicacion')
                ?: 'Estamos en el local principal configurado para Pollos y Parrillas El Dorado. Puedes revisar la seccion Ubicacion de la app para abrir el mapa.';
        }

        if ($this->matchesAny($normalized, ['horario', 'hora', 'atienden', 'abren', 'cierran', 'atencion'])) {
            $hours = (string) config('chatbot.hours');
            $brand = (string) config('chatbot.brand_name');

            return "Horario de atencion de {$brand}: {$hours}.";
        }

        if ($this->matchesAny($normalized, ['contacto', 'telefono', 'numero', 'llamar', 'whatsapp', 'correo', 'email', 'soporte'])) {
            return $this->contactLine();
        }

        if ($this->matchesAny($normalized, ['pago', 'pagos', 'izipay', 'tarjeta', 'qr'])) {
            return $this->paymentHelp() ?: $this->contactLine();
        }

        if ($this->matchesAny($normalized, ['delivery', 'envio', 'envios', 'reparto'])) {
            return $this->knowledgeSection('Delivery')
                ?: 'Hacemos delivery y tambien recojo en local. Para delivery, indica tu direccion y una referencia visible.';
        }

        if ($this->matchesAny($normalized, ['pedido', 'pedidos', 'seguimiento', 'tracking', 'codigo', 'orden', 'ordenes'])) {
            return $this->knowledgeSection('Pedidos')
                ?: 'Para revisar tu pedido, entra a Mis pedidos y usa tu codigo de tracking.';
        }

        if ($this->matchesAny($normalized, ['barato', 'mas barato', 'menor precio', 'economico'])) {
            return $this->cheapestProducts();
        }

        if ($this->matchesAny($normalized, ['combinar', 'acompanar', 'combo', 'recomienda', 'recomendacion', 'con que'])) {
            return $this->comboSuggestion($normalized);
        }

        if ($this->matchesAny($normalized, ['pollos', 'parrillas', 'bebidas', 'menu', 'carta', 'productos', 'venden', 'ofrecen'])) {
            return $this->categoryListing($normalized);
        }

        return $this->generalHelp();
    }

    private function contactLine(): string
    {
        $brand = (string) config('chatbot.brand_name');
        $phone = (string) config('chatbot.support_phone');
        $email = (string) config('chatbot.support_email');
        $parts = array_filter([$phone, $email]);
        $contact = $parts ? implode(' o ', $parts) : 'nuestro soporte';

        return "Si necesitas ayuda humana, escribenos a {$contact} ({$brand}).";
    }

    private function paymentHelp(): ?string
    {
        $payments = (array) config('company.payments', []);
        $lines = [];

        if (($payments['izipay']['enabled'] ?? false)) {
            $label = trim((string) ($payments['izipay']['label'] ?? 'Izipay'));
            $message = trim((string) ($payments['izipay']['message'] ?? 'Paga con tarjeta desde el checkout seguro.'));
            $lines[] = "{$label}: {$message}";
        }

        if (($payments['cod']['enabled'] ?? false)) {
            $label = trim((string) ($payments['cod']['label'] ?? 'Pago contraentrega'));
            $message = trim((string) ($payments['cod']['message'] ?? 'Paga al recibir tu pedido en el lugar de entrega acordado.'));
            $lines[] = "{$label}: {$message}";
        }

        return $lines ? "Medios de pago\n".implode("\n", array_map(fn (string $line): string => "• {$line}", $lines)) : null;
    }

    private function cheapestProducts(): ?string
    {
        try {
            $items = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->orderBy('price')
                ->limit(3)
                ->get(['name', 'price', 'category']);
        } catch (\Throwable) {
            return null;
        }

        if ($items->isEmpty()) {
            return null;
        }

        $lines = $items->map(function (Product $product): string {
            $category = trim((string) $product->category);
            $suffix = $category !== '' ? " ({$category})" : '';

            return "{$product->name}{$suffix}: S/ ".number_format((float) $product->price, 2, '.', '');
        })->all();

        return "Opciones mas economicas ahora\n"
            .implode("\n", array_map(fn (string $line): string => "• {$line}", $lines))
            ."\n\nDime si buscas pollos, parrillas o bebidas y lo ajusto mejor.";
    }

    private function categoryListing(string $normalized): ?string
    {
        $category = null;
        if (Str::contains($normalized, 'pollos')) {
            $category = 'pollos';
        }
        if (Str::contains($normalized, 'parrillas')) {
            $category = 'parrillas';
        }
        if (Str::contains($normalized, 'bebidas')) {
            $category = 'bebidas';
        }
        if (! $category) {
            return $this->menuOverview();
        }

        try {
            $items = Product::query()
                ->where('is_available', true)
                ->where('category', $category)
                ->where('stock', '>', 0)
                ->orderBy('name')
                ->limit(12)
                ->get(['name', 'price', 'description']);
        } catch (\Throwable) {
            return null;
        }

        if ($items->isEmpty()) {
            return null;
        }

        $lines = $items
            ->map(fn (Product $product): string => $this->productLine($product))
            ->all();

        return "Algunos {$category} disponibles\n"
            .implode("\n", array_map(fn (string $line): string => "• {$line}", $lines))
            ."\n\nPuedo recomendarte el mas economico o una combinacion.";
    }

    private function comboSuggestion(string $normalized): ?string
    {
        $mentionedProducts = $this->findMentionedProducts($normalized);
        if (count($mentionedProducts) >= 2) {
            return $this->selectedCombinationReply($mentionedProducts, $normalized);
        }

        $product = $mentionedProducts[0] ?? $this->findMentionedProduct($normalized);

        try {
            $available = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0);
        } catch (\Throwable) {
            return null;
        }

        if (! $product) {
            return $this->guidedComboSuggestion();
        }

        $suggestions = [];
        $category = Str::lower((string) $product->category);

        if (in_array($category, ['pollos', 'parrillas'], true) && $this->mentionsDrink($normalized)) {
            $drink = $this->requestedDrinkProduct($normalized);
            if ($drink) {
                return $this->selectedCombinationReply([$product, $drink], $normalized);
            }
        }

        if (in_array($category, ['pollos', 'parrillas'], true)) {
            $drink = (clone $available)->where('category', 'bebidas')->orderBy('price')->first();
            if ($drink) {
                $suggestions[] = $this->productLine($drink);
            }

            $side = (clone $available)
                ->whereNotIn('category', ['pollos', 'parrillas', 'bebidas'])
                ->orderBy('price')
                ->first();
            if ($side) {
                $suggestions[] = $this->productLine($side);
            }
        } elseif ($category === 'bebidas') {
            $main = (clone $available)->whereIn('category', ['pollos', 'parrillas'])->orderBy('price')->first();
            if ($main) {
                $suggestions[] = $this->productLine($main);
            }
        }

        if (! $suggestions) {
            return "Para combinar con {$product->name}, una bebida fria o una guarnicion va muy bien. Prefieres algo personal o familiar?";
        }

        return "Combinacion recomendada para {$product->name}\n"
            .implode("\n", array_map(fn (string $line): string => "• {$line}", $suggestions))
            ."\n\nSi me dices si quieres algo personal, familiar o economico, puedo ajustar mejor la combinacion.";
    }

    private function menuOverview(): ?string
    {
        try {
            $items = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->orderBy('category')
                ->orderBy('name')
                ->limit(18)
                ->get(['name', 'price', 'category', 'description']);
        } catch (\Throwable) {
            return null;
        }

        if ($items->isEmpty()) {
            return null;
        }

        $groups = $items->groupBy(fn (Product $product): string => trim((string) $product->category) ?: 'general');
        $lines = [];

        foreach ($groups as $category => $products) {
            $lines[] = strtoupper((string) $category);
            foreach ($products->take(6) as $product) {
                $lines[] = "• ".$this->productLine($product);
            }
        }

        return "Tenemos estas opciones disponibles\n"
            .implode("\n", $lines)
            ."\n\nPuedo ayudarte a elegir una combinacion personal, familiar o economica.";
    }

    private function generalHelp(): string
    {
        $brand = (string) config('chatbot.brand_name');

        return "Soy POLL-IA de {$brand}. Puedo ayudarte con productos, precios, pagos, delivery, horario, ubicacion y seguimiento de pedidos. Prueba preguntarme: \"que productos tienen\", \"cuales son sus pagos\" o \"donde estan ubicados\".";
    }

    private function productLine(Product $product): string
    {
        $description = trim((string) $product->description);
        $descriptionText = $description !== '' ? " - {$description}" : '';

        return "{$product->name} - S/ ".number_format((float) $product->price, 2, '.', '')."{$descriptionText}";
    }

    private function guidedComboSuggestion(): ?string
    {
        try {
            $main = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->whereIn('category', ['pollos', 'parrillas'])
                ->orderBy('price')
                ->first(['name', 'price', 'category', 'description']);

            $drink = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->where('category', 'bebidas')
                ->orderBy('price')
                ->first(['name', 'price', 'category', 'description']);
        } catch (\Throwable) {
            return null;
        }

        if (! $main) {
            return $this->cheapestProducts();
        }

        $lines = [$this->productLine($main)];
        if ($drink) {
            $lines[] = $this->productLine($drink);
        }

        return "Para empezar, te sugiero esta combinacion\n"
            .implode("\n", array_map(fn (string $line): string => "• {$line}", $lines))
            ."\n\nTambien puedo ayudarte a elegir por presupuesto, cantidad de personas o antojo.";
    }

    private function selectedCombinationReply(array $products, string $normalized): string
    {
        $products = array_values(array_slice($products, 0, 4));
        $lines = array_map(fn (Product $product): string => "â€¢ ".$this->productLine($product), $products);
        $total = array_reduce(
            $products,
            fn (float $carry, Product $product): float => $carry + (float) $product->price,
            0.0
        );
        $items = array_map(fn (Product $product): array => [
            'product' => $product,
            'qty' => $this->quantityForProduct($normalized, $product),
        ], $products);
        $lines = array_map(function (array $item): string {
            $qtyText = $item['qty'] > 1 ? "{$item['qty']} x " : '';

            return "â€¢ ".$qtyText.$this->productLine($item['product']);
        }, $items);
        $total = array_reduce(
            $items,
            fn (float $carry, array $item): float => $carry + ((float) $item['product']->price * (int) $item['qty']),
            0.0
        );

        $contactNote = preg_match('/\b9\d{8}\b/', $normalized)
            ? "\n\nVeo que tambien escribiste un numero de contacto. Usalo o confirmalo en el checkout para evitar errores de entrega."
            : '';

        return "Buena eleccion. Tu combinacion quedaria asi:\n"
            .implode("\n", $lines)
            ."\n\nTotal referencial: S/ ".number_format($total, 2, '.', '')
            ."\n\nSi estas conforme, agregalos al carrito desde la tienda y continua con el pago."
            .$contactNote;
    }

    private function quantityForProduct(string $normalized, Product $product): int
    {
        $category = Str::lower((string) $product->category);
        $name = $this->normalize($product->name);
        $words = $this->meaningfulProductWords($name);
        $quantity = 1;

        if ($category === 'bebidas') {
            foreach (['gaseosas?', 'bebidas?', 'cocas?', 'colas?', 'coca cola', 'inca cola', 'sprite', 'aguas?'] as $term) {
                if (preg_match('/\b([1-9]\d?)\s+(?:de\s+)?'.$term.'\b/u', $normalized, $match)) {
                    $quantity = max($quantity, (int) $match[1]);
                }
            }
        }

        foreach ($words as $word) {
            if (preg_match('/\b([1-9]\d?)\s*(?:x\s*)?(?:\w+\s+){0,3}'.preg_quote($word, '/').'\b/u', $normalized, $match)) {
                $quantity = max($quantity, (int) $match[1]);
            }
        }

        return min(20, max(1, $quantity));
    }

    private function mentionsDrink(string $normalized): bool
    {
        return $this->matchesAny($normalized, [
            'gaseosa',
            'gaseosas',
            'bebida',
            'bebidas',
            'coca cola',
            'inca cola',
            'sprite',
        ]);
    }

    private function requestedDrinkProduct(string $normalized): ?Product
    {
        try {
            $drinks = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->where('category', 'bebidas')
                ->orderBy('price')
                ->limit(30)
                ->get(['id', 'name', 'category', 'price', 'description']);
        } catch (\Throwable) {
            return null;
        }

        $best = null;
        $bestScore = 0;

        foreach ($drinks as $drink) {
            $name = $this->normalize($drink->name);
            $score = $this->productMentionScore($normalized, $name);

            if ($this->matchesAny($normalized, ['gaseosa', 'gaseosas', 'bebida', 'bebidas'])
                && $this->matchesAny($name, ['coca cola', 'inca cola', 'sprite', 'gaseosa'])) {
                $score += 2;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $drink;
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    private function findMentionedProduct(string $normalized): ?Product
    {
        return $this->findMentionedProducts($normalized)[0] ?? null;
    }

    /**
     * Detects product mentions with light fuzzy matching, so phrases like
     * "combo familiar + inca cola" match "Mega Combo Familiar" and "Inca Kola".
     */
    private function findMentionedProducts(string $normalized): array
    {
        try {
            $candidates = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->limit(80)
                ->get(['id', 'name', 'category', 'price', 'description']);
        } catch (\Throwable) {
            return [];
        }

        $matches = [];

        foreach ($candidates as $product) {
            $name = $this->normalize($product->name);
            if ($name === '') {
                continue;
            }

            if (Str::contains($normalized, $name) || Str::contains($name, $normalized)) {
                $matches[] = ['score' => 100 + strlen($name), 'product' => $product];
                continue;
            }

            $score = $this->productMentionScore($normalized, $name);
            if ($score >= 2) {
                $matches[] = ['score' => $score, 'product' => $product];
            }
        }

        usort($matches, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $products = [];
        $seen = [];
        foreach ($matches as $match) {
            $product = $match['product'];
            if (isset($seen[$product->id])) {
                continue;
            }
            $seen[$product->id] = true;
            $products[] = $product;
        }

        return $products;
    }

    private function productMentionScore(string $message, string $productName): int
    {
        if (
            preg_match('/\b\d+(?:\.\d+)?(?:ml|l)\b/u', $message)
            && preg_match('/\b\d+(?:\.\d+)?(?:ml|l)\b/u', $productName, $unit)
            && ! Str::contains($message, $unit[0])
        ) {
            return 0;
        }

        $words = $this->meaningfulProductWords($productName);

        if (! $words) {
            return 0;
        }

        $score = 0;
        foreach ($words as $word) {
            if (Str::contains($message, $word)) {
                $score++;
            }
        }

        return $score;
    }

    private function meaningfulProductWords(string $productName): array
    {
        $words = preg_split('/\s+/', $productName) ?: [];

        return array_values(array_filter($words, function (string $word): bool {
            return strlen($word) >= 4 && ! in_array($word, [
                'brasa',
                'personal',
                'tradicional',
                'bebida',
                'bebidas',
                'helada',
                'papas',
                'ensalada',
                'gaseosa',
                'gaseosas',
                'pollo',
            ], true);
        }));
    }

    private function knowledgeSection(string $title): ?string
    {
        $path = (string) config('chatbot.knowledge_path');
        if ($path === '' || ! is_file($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        $raw = is_string($raw) ? $raw : '';
        if (trim($raw) === '') {
            return null;
        }

        $lines = preg_split("/\\r\\n|\\r|\\n/", $raw) ?: [];
        $start = null;
        $pattern = '/^##\\s+'.preg_quote($title, '/').'\\s*$/iu';

        foreach ($lines as $idx => $line) {
            if (preg_match($pattern, trim((string) $line))) {
                $start = $idx + 1;
                break;
            }
        }

        if ($start === null) {
            return null;
        }

        $out = [];
        for ($i = $start; $i < count($lines); $i++) {
            $line = trim((string) $lines[$i]);
            if ($line === '') {
                continue;
            }
            if (Str::startsWith($line, '#')) {
                break;
            }

            $line = preg_replace('/^[-*]\\s*/', '', $line);
            $line = str_replace('**', '', (string) $line);
            if (trim($line) !== '') {
                $out[] = trim($line);
            }
        }

        return $out ? implode("\n", $out) : null;
    }

    private function normalize(string $text): string
    {
        $text = Str::lower(trim($text));
        $text = Str::of($text)->ascii()->toString();
        $text = str_replace('kola', 'cola', $text);
        $text = preg_replace('/(\d+)\s*(ml|l)\b/u', '$1$2', (string) $text);
        $text = preg_replace('/[^a-z0-9\/.]+/u', ' ', (string) $text);
        $text = preg_replace('/\\s+/', ' ', $text);

        return trim((string) $text);
    }

    private function matchesAny(string $normalized, array $needles): bool
    {
        foreach ($needles as $needle) {
            $needle = $this->normalize((string) $needle);
            if ($needle !== '' && Str::contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isSensitiveRequest(string $normalized): bool
    {
        foreach ([
            'admin',
            'administrador',
            'credencial',
            'credenciales',
            'password',
            'contrasena',
            'clave',
            'secret',
            'token',
            'api key',
            'apikey',
            'base de datos',
            'database',
            'usuario',
            'usuarios',
            'clientes registrados',
            'correos de clientes',
            'telefonos de clientes',
            'dni de clientes',
            'direcciones de clientes',
            'reporte interno',
            'ventas internas',
            'cierre de caja',
        ] as $needle) {
            if (Str::contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }
}
