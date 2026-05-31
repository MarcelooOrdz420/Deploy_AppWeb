<?php

namespace App\Services\Chatbot;

use App\Models\Product;
use Illuminate\Support\Str;

class LocalResponder
{
    public function reply(string $message): ?string
    {
        $normalized = $this->normalize($message);

        if ($this->matchesAny($normalized, ['ubicacion', 'ubicacion de la tienda', 'direccion', 'donde', 'mapa', 'como llegar', 'google maps'])) {
            $section = $this->knowledgeSection('Ubicación');
            if ($section) return $section;
        }

        if ($this->matchesAny($normalized, ['horario', 'hora', 'atienden', 'abren', 'cierran', 'atencion'])) {
            $hours = (string) config('chatbot.hours');
            $brand = (string) config('chatbot.brand_name');
            return trim("Horario de atención de {$brand}: {$hours}.");
        }

        if ($this->matchesAny($normalized, ['contacto', 'telefono', 'numero', 'llamar', 'whatsapp', 'correo', 'email', 'soporte'])) {
            return $this->contactLine();
        }

        if ($this->matchesAny($normalized, ['pago', 'pagos', 'yape', 'plin', 'transferencia', 'contraentrega', 'qr'])) {
            $text = $this->paymentHelp();
            return $text ?: $this->contactLine();
        }

        if ($this->matchesAny($normalized, ['delivery', 'envio', 'envios', 'reparto'])) {
            $section = $this->knowledgeSection('Delivery');
            if ($section) return $section;
            return 'Hacemos delivery y también recojo en local. Para delivery, indícanos tu dirección y una referencia.';
        }

        if ($this->matchesAny($normalized, ['pedido', 'pedidos', 'seguimiento', 'tracking', 'codigo', 'orden', 'ordenes'])) {
            $section = $this->knowledgeSection('Pedidos');
            if ($section) return $section;
            return 'Para revisar tu pedido, entra a “Mis pedidos” y usa tu código de tracking.';
        }

        if ($this->matchesAny($normalized, ['barato', 'mas barato', 'más barato', 'menor precio', 'precio mas bajo', 'precio más bajo', 'economico', 'económico'])) {
            $text = $this->cheapestProducts();
            if ($text) return $text;
        }

        if ($this->matchesAny($normalized, ['combinar', 'acompanar', 'acompañar', 'combo', 'recomienda', 'recomendacion', 'recomendación', 'con que'])) {
            $text = $this->comboSuggestion($normalized);
            if ($text) return $text;
        }

        if ($this->matchesAny($normalized, ['pollos', 'parrillas', 'bebidas', 'menu', 'menú', 'carta', 'productos'])) {
            $text = $this->categoryListing($normalized);
            if ($text) return $text;
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

        return "Si necesitas ayuda humana, escríbenos a {$contact} ({$brand}).";
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
        if (($payments['transfer']['enabled'] ?? false) && ! empty($payments['transfer']['bank_name'])) {
            $bank = $payments['transfer']['bank_name'];
            $acc = $payments['transfer']['account_number'] ?? '';
            $cci = $payments['transfer']['cci'] ?? '';
            $detail = trim($acc) !== '' ? "Cuenta: {$acc}" : null;
            $detail2 = trim($cci) !== '' ? "CCI: {$cci}" : null;
            $lines[] = trim('Transferencia ('.$bank.'): '.implode(' · ', array_filter([$detail, $detail2])));
        }
        if (($payments['cod']['enabled'] ?? false)) {
            $msg = trim((string) ($payments['cod']['message'] ?? 'Pagas cuando recibes tu pedido.'));
            $lines[] = "Contraentrega: {$msg}";
        }

        if (! $lines) return null;
        return "Medios de pago:\n- ".implode("\n- ", $lines);
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

        if ($items->isEmpty()) return null;

        $lines = $items->map(function (Product $p) {
            $category = trim((string) $p->category);
            $suffix = $category !== '' ? " ({$category})" : '';
            return "{$p->name}{$suffix}: S/ ".number_format((float) $p->price, 2, '.', '');
        })->all();

        return "Opciones más económicas ahora:\n- ".implode("\n- ", $lines)."\n¿De qué categoría te provoca (pollos, parrillas o bebidas)?";
    }

    private function categoryListing(string $normalized): ?string
    {
        $category = null;
        if (Str::contains($normalized, 'pollos')) $category = 'pollos';
        if (Str::contains($normalized, 'parrillas')) $category = 'parrillas';
        if (Str::contains($normalized, 'bebidas')) $category = 'bebidas';

        if (! $category) return null;

        try {
            $items = Product::query()
                ->where('is_available', true)
                ->where('category', $category)
                ->orderBy('price')
                ->limit(6)
                ->get(['name', 'price']);
        } catch (\Throwable) {
            return null;
        }

        if ($items->isEmpty()) return null;

        $lines = $items->map(fn (Product $p) => "{$p->name}: S/ ".number_format((float) $p->price, 2, '.', ''))->all();
        return "Algunos {$category}:\n- ".implode("\n- ", $lines)."\n¿Quieres ver el más barato o recomendar un combo?";
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

        if ($product) {
            $category = Str::lower((string) $product->category);
            $suggestions = [];

            if (in_array($category, ['pollos', 'parrillas'], true)) {
                $drink = (clone $available)->where('category', 'bebidas')->orderBy('price')->first();
                if ($drink) $suggestions[] = "{$drink->name} (bebida) · S/ ".number_format((float) $drink->price, 2, '.', '');

                $extra = (clone $available)->whereNotIn('category', ['bebidas', $category])->orderBy('price')->first();
                if ($extra) $suggestions[] = "{$extra->name} · S/ ".number_format((float) $extra->price, 2, '.', '');
            } elseif ($category === 'bebidas') {
                $main = (clone $available)->whereIn('category', ['pollos', 'parrillas'])->orderBy('price')->first();
                if ($main) $suggestions[] = "{$main->name} ({$main->category}) · S/ ".number_format((float) $main->price, 2, '.', '');
            }

            if ($suggestions) {
                return "Para combinar con “{$product->name}”, te recomiendo:\n- ".implode("\n- ", $suggestions)."\n¿Te lo agrego al carrito?";
            }

            return "Para combinar con “{$product->name}”, una bebida fría siempre va bien. ¿Prefieres chicha, gaseosa o limonada?";
        }

        $cheap = $this->cheapestProducts();
        if ($cheap) return $cheap;
        return null;
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
        foreach ($candidates as $p) {
            $name = $this->normalize($p->name);
            if ($name === '') continue;

            if (Str::contains($normalized, $name) || Str::contains($name, $normalized)) {
                $len = strlen($name);
                if ($len > $bestLen) {
                    $best = $p;
                    $bestLen = $len;
                }
                continue;
            }

            $words = array_filter(explode(' ', $name));
            if (count($words) >= 2) {
                $hits = 0;
                foreach ($words as $w) {
                    if (strlen($w) < 3) continue;
                    if (Str::contains($normalized, $w)) $hits++;
                }
                if ($hits >= 2 && strlen($name) > $bestLen) {
                    $best = $p;
                    $bestLen = strlen($name);
                }
            }
        }

        return $best;
    }

    private function knowledgeSection(string $title): ?string
    {
        $path = (string) config('chatbot.knowledge_path');
        if ($path === '' || ! is_file($path)) return null;
        $raw = @file_get_contents($path);
        $raw = is_string($raw) ? $raw : '';
        if (trim($raw) === '') return null;

        $lines = preg_split("/\\r\\n|\\r|\\n/", $raw) ?: [];
        $start = null;
        $pattern = '/^##\\s+'.preg_quote($title, '/').'\\s*$/iu';
        foreach ($lines as $idx => $line) {
            if (preg_match($pattern, trim((string) $line))) {
                $start = $idx + 1;
                break;
            }
        }
        if ($start === null) return null;

        $out = [];
        for ($i = $start; $i < count($lines); $i++) {
            $line = (string) $lines[$i];
            $trim = trim($line);
            if ($trim === '') continue;
            if (Str::startsWith($trim, '#')) break;
            $trim = preg_replace('/^[-*]\\s*/', '', $trim);
            $trim = str_replace('**', '', $trim);
            $trim = trim((string) $trim);
            if ($trim !== '') $out[] = $trim;
        }

        if (! $out) return null;
        return implode("\n", $out);
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
        foreach ($needles as $n) {
            $n = $this->normalize((string) $n);
            if ($n !== '' && Str::contains($normalized, $n)) return true;
        }
        return false;
    }
}

