<?php

namespace App\Services\Chatbot;

use App\Models\Product;
use Illuminate\Support\Str;

class LocalResponder
{
    public function reply(string $message): ?string
    {
        $normalized = $this->normalize($message);

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

        if ($this->matchesAny($normalized, ['pago', 'pagos', 'yape', 'plin', 'mercado pago', 'contraentrega', 'qr'])) {
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

        if ($this->matchesAny($normalized, ['pollos', 'parrillas', 'bebidas', 'menu', 'carta', 'productos'])) {
            return $this->categoryListing($normalized);
        }

        return null;
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

        if (($payments['yape']['enabled'] ?? false) && ! empty($payments['yape']['phone'])) {
            $lines[] = "Yape: {$payments['yape']['phone']}";
        }
        if (($payments['plin']['enabled'] ?? false) && ! empty($payments['plin']['phone'])) {
            $lines[] = "Plin: {$payments['plin']['phone']}";
        }
        if (($payments['mercado_pago']['enabled'] ?? false)) {
            $label = trim((string) ($payments['mercado_pago']['label'] ?? 'Mercado Pago'));
            $lines[] = "{$label}: checkout seguro para tarjetas, cuenta Mercado Pago y Yape.";
        }
        if (($payments['cod']['enabled'] ?? false)) {
            $msg = trim((string) ($payments['cod']['message'] ?? 'Pagas cuando recibes tu pedido.'));
            $lines[] = "Contraentrega: {$msg}";
        }

        return $lines ? "Medios de pago:\n- ".implode("\n- ", $lines) : null;
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

        return "Opciones mas economicas ahora:\n- ".implode("\n- ", $lines)."\nQue categoria te provoca: pollos, parrillas o bebidas?";
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
            return null;
        }

        try {
            $items = Product::query()
                ->where('is_available', true)
                ->where('category', $category)
                ->where('stock', '>', 0)
                ->orderBy('price')
                ->limit(6)
                ->get(['name', 'price']);
        } catch (\Throwable) {
            return null;
        }

        if ($items->isEmpty()) {
            return null;
        }

        $lines = $items
            ->map(fn (Product $product): string => "{$product->name}: S/ ".number_format((float) $product->price, 2, '.', ''))
            ->all();

        return "Algunos {$category} disponibles:\n- ".implode("\n- ", $lines)."\nQuieres ver el mas barato o una recomendacion?";
    }

    private function comboSuggestion(string $normalized): ?string
    {
        $product = $this->findMentionedProduct($normalized);

        try {
            $available = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0);
        } catch (\Throwable) {
            return null;
        }

        if (! $product) {
            return $this->cheapestProducts();
        }

        $suggestions = [];
        $category = Str::lower((string) $product->category);

        if (in_array($category, ['pollos', 'parrillas'], true)) {
            $drink = (clone $available)->where('category', 'bebidas')->orderBy('price')->first();
            if ($drink) {
                $suggestions[] = "{$drink->name} (bebida) - S/ ".number_format((float) $drink->price, 2, '.', '');
            }
        } elseif ($category === 'bebidas') {
            $main = (clone $available)->whereIn('category', ['pollos', 'parrillas'])->orderBy('price')->first();
            if ($main) {
                $suggestions[] = "{$main->name} ({$main->category}) - S/ ".number_format((float) $main->price, 2, '.', '');
            }
        }

        if (! $suggestions) {
            return "Para combinar con {$product->name}, una bebida fria o una guarnicion va muy bien. Prefieres algo personal o familiar?";
        }

        return "Para combinar con {$product->name}, te recomiendo:\n- ".implode("\n- ", $suggestions);
    }

    private function findMentionedProduct(string $normalized): ?Product
    {
        try {
            $candidates = Product::query()
                ->where('is_available', true)
                ->limit(80)
                ->get(['id', 'name', 'category', 'price']);
        } catch (\Throwable) {
            return null;
        }

        $best = null;
        $bestLen = 0;

        foreach ($candidates as $product) {
            $name = $this->normalize($product->name);
            if ($name === '') {
                continue;
            }

            if (Str::contains($normalized, $name) || Str::contains($name, $normalized)) {
                $len = strlen($name);
                if ($len > $bestLen) {
                    $best = $product;
                    $bestLen = $len;
                }
            }
        }

        return $best;
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
}
