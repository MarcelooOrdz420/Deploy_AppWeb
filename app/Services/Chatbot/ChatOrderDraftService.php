<?php

namespace App\Services\Chatbot;

use App\Models\ChatOrderDraft;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChatOrderDraftService
{
    public function capture(string $message, ?User $user, ?string $guestSession): ?array
    {
        if (! $this->tableExists()) {
            return null;
        }

        $draft = $this->draftFor($user, $guestSession);
        if (! $draft) {
            return null;
        }

        $normalized = $this->normalize($message);
        $metadata = is_array($draft->metadata) ? $draft->metadata : [];
        $items = is_array($draft->items) ? $draft->items : [];
        $purchaseRelated = $this->isPurchaseRelated($normalized);
        $metadata = $this->appendCustomerMessage($metadata, $message, $purchaseRelated);
        $changed = true;
        $orderActivity = false;

        if ($email = $this->extractEmail($message)) {
            $draft->email = $email;
            $metadata['last_email_detected'] = $email;
            $changed = true;
            $orderActivity = true;
        }

        if ($phone = $this->extractPhone($message)) {
            $draft->phone = $phone;
            $metadata['last_phone_detected'] = $phone;
            $changed = true;
            $orderActivity = true;
        }

        $matches = $this->findMentionedProducts($normalized);
        if ($matches) {
            $metadata['last_product_message'] = Str::limit($message, 1200, '');
            foreach ($matches as $product) {
                $qty = $this->quantityForProduct($normalized, $product, $metadata);
                $items = $this->mergeItem($items, $product, $qty);
            }
            unset($metadata['pending_drink_qty']);
            $changed = true;
            $orderActivity = true;
        } elseif ($this->mentionsGenericDrink($normalized)) {
            $metadata['pending_drink_qty'] = $this->quantityFromText($normalized) ?: 1;
            $metadata['last_product_message'] = Str::limit($message, 1200, '');
            $changed = true;
            $orderActivity = true;
        }

        if ($address = $this->extractAddress($message, $orderActivity || ! empty($items))) {
            if ($this->looksLikeReference($address)) {
                $draft->delivery_reference = $address;
                $metadata['last_reference_message'] = Str::limit($message, 1200, '');
            } else {
                $draft->delivery_address = $address;
                $metadata['last_address_message'] = Str::limit($message, 1200, '');
            }
            $changed = true;
            $orderActivity = true;
        }

        if (! $changed) {
            return null;
        }

        $draft->items = array_values($items);
        $draft->metadata = $metadata;
        $draft->status = 'active';
        $draft->last_message_at = now();
        $draft->save();

        return [
            'draft' => $draft->fresh(),
            'order_activity' => $orderActivity,
            'reply' => $this->replyFor($draft->fresh()),
            'snapshot' => $this->snapshotFromDraft($draft->fresh()),
        ];
    }

    public function contextFor(?User $user, ?string $guestSession): ?string
    {
        if (! $this->tableExists()) {
            return null;
        }

        $draft = $this->draftFor($user, $guestSession, false);
        $guestSession = trim((string) $guestSession);
        if (! $draft && $guestSession !== '') {
            $draft = ChatOrderDraft::query()
                ->where('guest_session', $guestSession)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        if (! $draft) {
            return null;
        }

        $parts = [];
        foreach ((array) $draft->items as $item) {
            $parts[] = "{$item['qty']} x {$item['name']}";
        }

        $metadata = (array) $draft->metadata;
        if (! empty($metadata['pending_drink_qty'])) {
            $parts[] = "{$metadata['pending_drink_qty']} gaseosas pendientes de marca";
        }

        return trim(implode("\n", array_filter([
            $parts ? 'Productos ya indicados: '.implode(', ', $parts) : null,
            $draft->delivery_address ? "Direccion indicada: {$draft->delivery_address}" : null,
            $draft->delivery_reference ? "Referencia indicada: {$draft->delivery_reference}" : null,
            $draft->phone ? "Telefono indicado: {$draft->phone}" : null,
            $draft->email ? "Correo indicado: {$draft->email}" : null,
        ]))) ?: null;
    }

    public function markConverted(?User $user, ?string $guestSession): void
    {
        if (! $this->tableExists()) {
            return;
        }

        if ($user) {
            ChatOrderDraft::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'converted']);
        }

        $guestSession = trim((string) $guestSession);
        if ($guestSession !== '') {
            ChatOrderDraft::query()
                ->where('guest_session', $guestSession)
                ->where('status', 'active')
                ->update(['status' => 'converted']);
        }
    }

    public function snapshotFor(?User $user, ?string $guestSession): ?array
    {
        if (! $this->tableExists()) {
            return null;
        }

        $draft = $this->draftFor($user, $guestSession, false);
        $guestSession = trim((string) $guestSession);
        if (! $draft && $guestSession !== '') {
            $draft = ChatOrderDraft::query()
                ->where('guest_session', $guestSession)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        if (! $draft) {
            return null;
        }

        return $this->snapshotFromDraft($draft);
    }

    private function snapshotFromDraft(ChatOrderDraft $draft): array
    {
        return [
            'email' => $draft->email,
            'phone' => $draft->phone,
            'delivery_address' => $draft->delivery_address,
            'delivery_reference' => $draft->delivery_reference,
            'items' => $draft->items ?: [],
        ];
    }

    private function draftFor(?User $user, ?string $guestSession, bool $create = true): ?ChatOrderDraft
    {
        if ($user) {
            return $create
                ? ChatOrderDraft::query()->firstOrCreate(['user_id' => $user->id, 'status' => 'active'])
                : ChatOrderDraft::query()->where('user_id', $user->id)->where('status', 'active')->latest()->first();
        }

        $guestSession = trim((string) $guestSession);
        if ($guestSession === '') {
            return null;
        }

        return $create
            ? ChatOrderDraft::query()->firstOrCreate(['guest_session' => $guestSession, 'status' => 'active'])
            : ChatOrderDraft::query()->where('guest_session', $guestSession)->where('status', 'active')->latest()->first();
    }

    private function appendCustomerMessage(array $metadata, string $message, bool $purchaseRelated): array
    {
        $entry = [
            'text' => Str::limit(trim($message), 1200, ''),
            'purchase_related' => $purchaseRelated,
            'captured_at' => now()->toIso8601String(),
        ];

        $messages = is_array($metadata['customer_messages'] ?? null)
            ? $metadata['customer_messages']
            : [];
        $messages[] = $entry;
        $metadata['customer_messages'] = $messages;
        $metadata['last_customer_message'] = $entry;

        if ($purchaseRelated) {
            $purchaseMessages = is_array($metadata['purchase_messages'] ?? null)
                ? $metadata['purchase_messages']
                : [];
            $purchaseMessages[] = $entry;
            $metadata['purchase_messages'] = $purchaseMessages;
            $metadata['last_purchase_message'] = $entry;
        }

        return $metadata;
    }

    private function isPurchaseRelated(string $normalized): bool
    {
        if ($normalized === '') {
            return false;
        }

        foreach ([
            'quiero',
            'deseo',
            'pedido',
            'pedir',
            'comprar',
            'compra',
            'carrito',
            'delivery',
            'recojo',
            'direccion',
            'telefono',
            'correo',
            'boleta',
            'factura',
            'dni',
            'ruc',
            'pago',
            'yape',
            'plin',
            'mercado pago',
            'contraentrega',
            'pollo',
            'parrilla',
            'gaseosa',
            'bebida',
            'combo',
            'mostrito',
        ] as $needle) {
            if (Str::contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function replyFor(ChatOrderDraft $draft): ?string
    {
        $items = (array) $draft->items;
        $metadata = (array) $draft->metadata;

        if (! $items && empty($metadata['pending_drink_qty'])) {
            return null;
        }

        $lines = [];
        $total = 0.0;
        foreach ($items as $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = (float) ($item['price'] ?? 0);
            $total += $qty * $price;
            $qtyText = $qty > 1 ? "{$qty} x " : '';
            $description = trim((string) ($item['description'] ?? ''));
            $descriptionText = $description !== '' ? " - {$description}" : '';
            $lines[] = "- {$qtyText}{$item['name']} - S/ ".number_format($price, 2, '.', '').$descriptionText;
        }

        if (! empty($metadata['pending_drink_qty'])) {
            $lines[] = "- {$metadata['pending_drink_qty']} gaseosas pendientes de marca: dime Coca-Cola, Inca Kola o Sprite.";
        }

        $missing = [];
        if (! $draft->delivery_address) {
            $missing[] = 'direccion';
        }
        if (! $draft->phone) {
            $missing[] = 'telefono';
        }
        if (! $draft->email) {
            $missing[] = 'correo';
        }

        $upsell = $this->needsComplementOffer($items, $metadata)
            ? "\n\nQuieres agregar alguna bebida o algun otro platillo antes de continuar?"
            : '';

        $next = $missing
            ? 'Para continuar falta: '.implode(', ', $missing).'.'.$upsell
            : 'Ya tengo productos, direccion, telefono y correo. Si estas conforme, inicia sesion o crea cuenta para pasar directo al carrito.';

        return "Buena eleccion. Voy guardando tu pedido temporal:\n"
            .implode("\n", $lines)
            ."\n\nTotal referencial: S/ ".number_format($total, 2, '.', '')
            ."\n\n{$next}";
    }

    private function needsComplementOffer(array $items, array $metadata): bool
    {
        if (! empty($metadata['pending_drink_qty'])) {
            return false;
        }

        $categories = collect($items)
            ->map(fn (array $item): string => Str::lower((string) ($item['category'] ?? '')))
            ->all();

        return in_array('pollos', $categories, true) || in_array('parrillas', $categories, true);
    }

    private function findMentionedProducts(string $normalized): array
    {
        try {
            $products = Product::query()
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->limit(80)
                ->get(['id', 'name', 'category', 'price', 'description', 'image_url']);
        } catch (\Throwable) {
            return [];
        }

        $matches = [];
        foreach ($products as $product) {
            $name = $this->normalize($product->name);
            $score = 0;

            if ($name !== '' && Str::contains($normalized, $name)) {
                $score = 100 + strlen($name);
            } else {
                $score = $this->mentionScore($normalized, $name);
            }

            if ($score >= 2) {
                $matches[] = ['score' => $score, 'product' => $product];
            }
        }

        usort($matches, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $seen = [];
        $out = [];
        foreach ($matches as $match) {
            $product = $match['product'];
            if (isset($seen[$product->id])) {
                continue;
            }
            $seen[$product->id] = true;
            $out[] = $product;
        }

        return array_slice($out, 0, 4);
    }

    private function mergeItem(array $items, Product $product, int $qty): array
    {
        foreach ($items as &$item) {
            if ((int) ($item['id'] ?? 0) === (int) $product->id) {
                $item['qty'] = max((int) $item['qty'], $qty);

                return $items;
            }
        }

        $items[] = [
            'id' => (int) $product->id,
            'name' => (string) $product->name,
            'category' => (string) $product->category,
            'price' => (float) $product->price,
            'qty' => max(1, $qty),
            'description' => (string) $product->description,
            'image_url' => (string) $product->image_url,
        ];

        return $items;
    }

    private function quantityForProduct(string $normalized, Product $product, array $metadata): int
    {
        $category = Str::lower((string) $product->category);
        $qty = $this->quantityFromText($normalized) ?: 1;

        if ($category === 'bebidas' && ! $this->hasExplicitQuantityNearDrink($normalized) && ! empty($metadata['pending_drink_qty'])) {
            $qty = (int) $metadata['pending_drink_qty'];
        }

        return min(20, max(1, $qty));
    }

    private function quantityFromText(string $normalized): ?int
    {
        return preg_match('/\b([1-9]\d?)\s*(?:x|unidades?|gaseosas?|bebidas?)?\b/u', $normalized, $match)
            ? (int) $match[1]
            : null;
    }

    private function hasExplicitQuantityNearDrink(string $normalized): bool
    {
        return (bool) preg_match('/\b[1-9]\d?\s+(?:gaseosas?|bebidas?|coca|cola|inca|sprite)\b/u', $normalized);
    }

    private function mentionScore(string $message, string $productName): int
    {
        if (
            preg_match('/\b\d+(?:\.\d+)?(?:ml|l)\b/u', $message)
            && preg_match('/\b\d+(?:\.\d+)?(?:ml|l)\b/u', $productName, $unit)
            && ! Str::contains($message, $unit[0])
        ) {
            return 0;
        }

        if (! $this->hasCategoryIntentForProduct($message, $productName)) {
            return 0;
        }

        $words = preg_split('/\s+/', $productName) ?: [];
        $ignore = ['brasa', 'personal', 'tradicional', 'bebida', 'bebidas', 'helada', 'papas', 'ensalada', 'pollo', 'cola'];
        $score = 0;

        foreach ($words as $word) {
            if (strlen($word) >= 4 && ! in_array($word, $ignore, true) && Str::contains($message, $word)) {
                $score++;
            }
        }

        return $score;
    }

    private function hasCategoryIntentForProduct(string $message, string $productName): bool
    {
        $isDrink = Str::contains($productName, ['coca', 'inca', 'sprite', 'agua', 'chicha', 'limonada', 'maracuya']);
        if ($isDrink) {
            return $this->mentionsGenericDrink($message)
                || Str::contains($message, ['coca', 'inca', 'sprite', 'agua', 'chicha', 'limonada', 'maracuya']);
        }

        return true;
    }

    private function mentionsGenericDrink(string $normalized): bool
    {
        return (bool) preg_match('/\b(?:gaseosa|gaseosas|bebida|bebidas)\b/u', $normalized);
    }

    private function extractEmail(string $message): ?string
    {
        return preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $message, $match)
            ? strtolower($match[0])
            : null;
    }

    private function extractPhone(string $message): ?string
    {
        return preg_match('/\b9\d{8}\b/', $message, $match) ? $match[0] : null;
    }

    private function extractAddress(string $message, bool $hasOrderContext): ?string
    {
        $clean = trim((string) preg_replace('/\b9\d{8}\b|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '', $message));
        $parts = preg_split('/[;,\n]+/', $clean) ?: [];

        foreach ($parts as $part) {
            $part = trim($part);
            $normalized = $this->normalize($part);
            if ($part === '' || strlen($normalized) < 5) {
                continue;
            }
            if ($this->findMentionedProducts($normalized) || $this->mentionsGenericDrink($normalized)) {
                continue;
            }
            if ($this->looksLikeAddress($normalized) || ($hasOrderContext && str_word_count($normalized) >= 2)) {
                return Str::limit($part, 500, '');
            }
        }

        return null;
    }

    private function looksLikeAddress(string $normalized): bool
    {
        return (bool) preg_match('/\b(?:jr|jiron|av|avenida|calle|pasaje|mz|lote|urb|lima|arequipa|cementerio|parque)\b/u', $normalized);
    }

    private function looksLikeReference(string $address): bool
    {
        return (bool) preg_match('/\b(?:cerca|frente|costado|lado|referencia|cementerio|parque|farmacia)\b/iu', $address);
    }

    private function normalize(string $text): string
    {
        $text = Str::lower(trim($text));
        $text = Str::of($text)->ascii()->toString();
        $text = str_replace('kola', 'cola', $text);
        $text = preg_replace('/(\d+)\s*(ml|l)\b/u', '$1$2', (string) $text);
        $text = preg_replace('/[^a-z0-9\/.]+/u', ' ', (string) $text);
        $text = preg_replace('/\s+/', ' ', (string) $text);

        return trim((string) $text);
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('chat_order_drafts');
        } catch (\Throwable) {
            return false;
        }
    }
}
