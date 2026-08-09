<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class ElectronicInvoiceService
{
    public function __construct(
        private readonly ApisPeruFacturationService $apisPeru,
        private readonly NubefactService $nubefact,
    ) {
    }

    public function sendInvoice(Order $order, bool $force = false): array
    {
        return $this->provider() === 'nubefact'
            ? $this->nubefact->sendInvoice($order, $force)
            : $this->apisPeru->sendInvoice($order, $force);
    }

    public function sendIfEligible(Order $order): array
    {
        $order->loadMissing('user');

        if (! (bool) config('einvoice.auto_send', false)) {
            return ['ok' => false, 'eligible' => false, 'reason' => 'automatic_send_disabled'];
        }
        if ((string) $order->payment_status !== 'verified' || ! $order->payment_verified_at) {
            return ['ok' => false, 'eligible' => false, 'reason' => 'payment_not_verified'];
        }
        if (trim((string) $order->payment_reference) === '') {
            return ['ok' => false, 'eligible' => false, 'reason' => 'missing_transaction_reference'];
        }
        if ((string) $order->status === Order::STATUS_CANCELLED) {
            return ['ok' => false, 'eligible' => false, 'reason' => 'order_cancelled'];
        }
        if (! in_array((string) $order->billing_receipt_type, ['boleta', 'factura'], true)) {
            return ['ok' => false, 'eligible' => false, 'reason' => 'receipt_not_requested'];
        }
        if (trim((string) ($order->billing_email ?: $order->customer_email ?: $order->user?->email)) === '') {
            return ['ok' => false, 'eligible' => false, 'reason' => 'missing_recipient'];
        }

        $lock = Cache::lock('einvoice:order:'.$order->id, 60);
        if (! $lock->get()) {
            return ['ok' => false, 'eligible' => true, 'reason' => 'already_processing'];
        }

        try {
            $order->refresh();
            if (! empty(data_get($order->billing_metadata, 'einvoice.sent_at'))) {
                return ['ok' => true, 'eligible' => true, 'already_sent' => true];
            }

            $metadata = $order->billing_metadata ?? [];
            $metadata['einvoice'] = array_merge($metadata['einvoice'] ?? [], [
                'status' => 'processing',
                'last_attempt_at' => now()->toIso8601String(),
                'automatic' => true,
            ]);
            $order->update(['billing_metadata' => $metadata]);

            return $this->sendInvoice($order->fresh(['items']));
        } catch (\Throwable $exception) {
            $order->refresh();
            $metadata = $order->billing_metadata ?? [];
            $metadata['einvoice'] = array_merge($metadata['einvoice'] ?? [], [
                'status' => 'failed',
                'last_attempt_at' => now()->toIso8601String(),
                'error' => 'No se pudo enviar el comprobante automaticamente.',
                'automatic' => true,
            ]);
            $order->update(['billing_metadata' => $metadata]);
            throw $exception;
        } finally {
            $lock->release();
        }
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
