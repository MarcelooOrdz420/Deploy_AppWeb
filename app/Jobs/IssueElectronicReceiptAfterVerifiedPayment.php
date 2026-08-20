<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ElectronicReceiptDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IssueElectronicReceiptAfterVerifiedPayment implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function handle(ElectronicReceiptDeliveryService $receiptDeliveryService): void
    {
        $order = Order::query()->find($this->orderId);

        if (! $order) {
            return;
        }

        $receiptDeliveryService->issueAfterVerifiedPayment($order);
    }
}
