<?php

namespace App\Services;

use App\Models\Order;
use RuntimeException;

class ElectronicInvoiceService
{
    public function __construct(
        private readonly ApisPeruFacturationService $apisPeru,
        private readonly NubefactService $nubefact,
    ) {
    }

    public function sendInvoice(Order $order): array
    {
        return $this->provider() === 'nubefact'
            ? $this->nubefact->sendInvoice($order)
            : $this->apisPeru->sendInvoice($order);
    }

    public function previewPayload(Order $order): array
    {
        return $this->provider() === 'nubefact'
            ? $this->nubefact->previewPayload($order)
            : $this->apisPeru->previewPayload($order);
    }

    private function provider(): string
    {
        $provider = strtolower(trim((string) config('einvoice.provider', 'apisperu')));

        if (! in_array($provider, ['apisperu', 'nubefact'], true)) {
            throw new RuntimeException("Proveedor de comprobantes no soportado: {$provider}.");
        }

        return $provider;
    }
}
