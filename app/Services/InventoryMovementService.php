<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class InventoryMovementService
{
    public function logProductOpening(Product $product, ?User $actor = null, ?string $note = null): void
    {
        $stock = max(0, (int) $product->stock);
        if ($stock === 0) {
            return;
        }

        $this->createMovement(
            product: $product,
            type: InventoryMovement::TYPE_OPENING,
            direction: InventoryMovement::DIRECTION_IN,
            quantity: $stock,
            stockBefore: 0,
            stockAfter: $stock,
            actor: $actor,
            note: $note ?: 'Stock inicial del producto',
        );
    }

    public function logManualStockAdjustment(Product $product, int $previousStock, int $newStock, ?User $actor = null, ?string $note = null): void
    {
        if ($previousStock === $newStock) {
            return;
        }

        $difference = abs($newStock - $previousStock);

        $this->createMovement(
            product: $product,
            type: InventoryMovement::TYPE_ADJUSTMENT,
            direction: $newStock > $previousStock ? InventoryMovement::DIRECTION_IN : InventoryMovement::DIRECTION_OUT,
            quantity: $difference,
            stockBefore: $previousStock,
            stockAfter: $newStock,
            actor: $actor,
            note: $note ?: 'Ajuste manual de stock',
        );
    }

    public function logSale(Order $order, Product $product, int $quantity, ?User $actor = null): void
    {
        $after = max(0, (int) $product->stock);
        $before = $after + $quantity;

        $this->createMovement(
            product: $product,
            type: InventoryMovement::TYPE_SALE,
            direction: InventoryMovement::DIRECTION_OUT,
            quantity: $quantity,
            stockBefore: $before,
            stockAfter: $after,
            actor: $actor,
            referenceType: 'order',
            referenceId: (int) $order->id,
            referenceCode: (string) $order->tracking_code,
            note: 'Salida por venta',
        );
    }

    public function logCancellationReturn(Order $order, Product $product, int $quantity, int $stockBefore, int $stockAfter, ?User $actor = null): void
    {
        $this->createMovement(
            product: $product,
            type: InventoryMovement::TYPE_CANCELLATION_RETURN,
            direction: InventoryMovement::DIRECTION_IN,
            quantity: $quantity,
            stockBefore: $stockBefore,
            stockAfter: $stockAfter,
            actor: $actor,
            referenceType: 'order',
            referenceId: (int) $order->id,
            referenceCode: (string) $order->tracking_code,
            note: 'Reposicion por cancelacion de pedido',
        );
    }

    private function createMovement(
        Product $product,
        string $type,
        string $direction,
        int $quantity,
        int $stockBefore,
        int $stockAfter,
        ?User $actor = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $referenceCode = null,
        ?string $note = null,
    ): void {
        InventoryMovement::query()->create([
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'movement_type' => $type,
            'direction' => $direction,
            'quantity' => max(0, $quantity),
            'stock_before' => max(0, $stockBefore),
            'stock_after' => max(0, $stockAfter),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reference_code' => $referenceCode,
            'note' => $note,
            'performed_by' => $actor?->id,
            'role_snapshot' => $actor?->role,
        ]);
    }
}
