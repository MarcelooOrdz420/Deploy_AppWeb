<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class IzipayPaymentConfirmationService
{
    public function confirm(string $krAnswerOriginal, string $hash, string $algorithm, ?int $expectedOrderId = null,
        string $hashKey = '', string $source = 'unknown', array $diagnostics = []): array
    {
        [$selectedKeyType, $key] = $this->signatureKey($hashKey);
        $calculatedHash = hash_hmac('sha256', $krAnswerOriginal, $key);
        $originalMatches = $hash !== '' && hash_equals(strtolower($calculatedHash), strtolower(trim($hash)));
        $rawurldecodedMatches = $hash !== '' && hash_equals(
            strtolower(hash_hmac('sha256', rawurldecode($krAnswerOriginal), $key)), strtolower(trim($hash))
        );
        Log::info('Izipay signature diagnostic', [
            'source' => $source,
            'original_matches' => $originalMatches,
            'rawurldecoded_matches' => $rawurldecodedMatches,
            'form_value_matches_raw_body' => $diagnostics['form_value_matches_raw_body'] ?? false,
            'relay_received' => $diagnostics['relay_received'] ?? false,
        ]);

        Log::info('Izipay signature validation metadata', [
            'source' => $source,
            'algorithm' => $algorithm,
            'hash_key' => $hashKey,
            'content_type' => $diagnostics['content_type'] ?? null,
            'kr_answer_length' => strlen($krAnswerOriginal),
            'received_hash_length' => strlen($hash),
            'calculated_hash_length' => strlen($calculatedHash),
            'selected_key_type' => $selectedKeyType,
            'selected_key_configured' => $key !== '',
            'selected_key_length' => strlen($key),
        ]);

        $algorithmMatches = strcasecmp($algorithm, 'sha256_hmac') === 0;
        $keyHasOuterWhitespace = $key !== trim($key);
        if ($key === '' || $hash === '' || $krAnswerOriginal === ''
            || ! $algorithmMatches || $selectedKeyType === 'unknown' || $keyHasOuterWhitespace || ! $originalMatches) {
            Log::warning('Izipay signature validation failed', [
                'algorithm' => $algorithm,
                'hash_key' => $hashKey,
                'kr_answer_length' => strlen($krAnswerOriginal),
                'received_hash_length' => strlen($hash),
                'calculated_hash_length' => strlen($calculatedHash),
                'selected_key_configured' => $key !== '',
            ]);
            throw new RuntimeException('Firma Izipay invalida.');
        }

        $payload = json_decode($krAnswerOriginal, true);
        if (! is_array($payload)) {
            throw new RuntimeException('Respuesta Izipay invalida.');
        }
        $reference = trim((string) (data_get($payload, 'orderDetails.orderId')
            ?: data_get($payload, 'orderId') ?: data_get($payload, 'metadata.tracking_code')));
        if ($reference === '') {
            throw new RuntimeException('Referencia Izipay ausente.');
        }

        return DB::transaction(function () use ($payload, $reference, $expectedOrderId): array {
            $order = Order::query()->where('tracking_code', $reference)->lockForUpdate()->first();
            if (! $order || ! $this->usesIzipay($order)) {
                throw new RuntimeException('Pedido Izipay no encontrado.');
            }
            if ($expectedOrderId !== null && $order->id !== $expectedOrderId) {
                throw new RuntimeException('El resultado no corresponde al pedido solicitado.');
            }
            if ($order->status === Order::STATUS_CANCELLED) {
                throw new RuntimeException('El pedido Izipay esta cancelado.');
            }
            $payment = PaymentTransaction::query()->where('order_id', $order->id)
                ->where('provider', 'izipay')->latest('id')->lockForUpdate()->first();
            if (! $payment) {
                Log::warning('Izipay transaction data mismatch', [
                    'shop_matches' => false, 'amount_matches' => false, 'currency_matches' => false,
                    'reference_matches' => $reference === (string) $order->tracking_code,
                    'merchant_order_matches' => false, 'transaction_matches' => false,
                    'payment_transaction_found' => false,
                ]);
                throw new RuntimeException('Intento de pago no encontrado.');
            }

            $shopId = trim((string) (data_get($payload, 'shopId') ?: data_get($payload, 'orderDetails.shopId')));
            $amount = data_get($payload, 'orderDetails.orderTotalAmount')
                ?? data_get($payload, 'orderDetails.amount') ?? data_get($payload, 'amount');
            $currency = strtoupper(trim((string) (data_get($payload, 'orderDetails.orderCurrency')
                ?: data_get($payload, 'orderDetails.currency') ?: data_get($payload, 'currency'))));
            $transactionId = trim((string) (data_get($payload, 'transactions.0.uuid')
                ?: data_get($payload, 'transactionDetails.cardDetails.legacyTransId') ?: data_get($payload, 'uuid')));
            $shopMatches = $shopId === trim((string) config('services.izipay.shop_id'));
            $expectedAmount = (int) round((float) $order->total_amount * 100, 0, PHP_ROUND_HALF_UP);
            $amountMatches = is_numeric($amount) && (int) $amount === $expectedAmount;
            $currencyMatches = $currency === 'PEN' && strtoupper((string) $payment->currency) === 'PEN';
            $referenceMatches = $reference === (string) $order->tracking_code;
            $merchantOrderMatches = $reference === (string) $payment->merchant_order_id;
            $transactionMatches = $transactionId !== '' && (trim((string) $payment->transaction_uuid) === ''
                || hash_equals((string) $payment->transaction_uuid, $transactionId));
            if (! $shopMatches || ! $amountMatches || ! $currencyMatches || ! $referenceMatches
                || ! $merchantOrderMatches || ! $transactionMatches) {
                Log::warning('Izipay transaction data mismatch', [
                    'shop_matches' => $shopMatches,
                    'amount_matches' => $amountMatches,
                    'currency_matches' => $currencyMatches,
                    'reference_matches' => $referenceMatches,
                    'merchant_order_matches' => $merchantOrderMatches,
                    'transaction_matches' => $transactionMatches,
                    'payment_transaction_found' => true,
                ]);
                throw new RuntimeException('Los datos de la transaccion no coinciden con el pedido.');
            }

            $existing = PaymentTransaction::query()->where('transaction_uuid', $transactionId)->lockForUpdate()->first();
            if ($existing && $existing->id !== $payment->id) {
                if ($existing->order_id === $order->id && $existing->status === 'verified') {
                    return ['order' => $order, 'status' => 'verified', 'duplicate' => true, 'transitioned' => false];
                }
                throw new RuntimeException('La transaccion ya pertenece a otro intento o pedido.');
            }

            $status = $this->paymentStatus($payload);
            if ($payment->status === 'verified' && $order->payment_status === 'verified') {
                return ['order' => $order, 'status' => 'verified', 'duplicate' => true, 'transitioned' => false];
            }
            $wasVerified = $order->payment_status === 'verified';
            $effectiveStatus = $wasVerified ? 'verified' : $status;
            $paymentStatus = $payment->status === 'verified' ? 'verified' : $status;
            $payment->update([
                'status' => $paymentStatus,
                'transaction_uuid' => $transactionId,
                'authorization_number' => data_get($payload, 'transactions.0.transactionDetails.cardDetails.authorizationResponse.authorizationResult'),
                'response_code' => (string) (data_get($payload, 'transactions.0.responseCode') ?: data_get($payload, 'transactions.0.detailedStatus') ?: data_get($payload, 'orderStatus')),
                'response_message' => (string) (data_get($payload, 'transactions.0.responseMessage') ?: data_get($payload, 'transactions.0.errorMessage') ?: data_get($payload, 'orderStatus')),
                'raw_response' => $this->sanitize($payload),
                'processed_at' => now(),
            ]);
            $order->forceFill([
                'payment_gateway' => $order->payment_gateway ?: 'izipay',
                'payment_reference' => $transactionId,
                'payment_status' => $effectiveStatus,
                'payment_reported_at' => $order->payment_reported_at ?: now(),
                'payment_verified_at' => $effectiveStatus === 'verified' ? ($order->payment_verified_at ?: now()) : $order->payment_verified_at,
            ])->save();
            Log::info('Izipay payment confirmation processed.', [
                'order_id' => $order->id, 'status' => $effectiveStatus, 'duplicate' => false,
            ]);

            return ['order' => $order->fresh(['items', 'statusHistory']), 'status' => $effectiveStatus,
                'duplicate' => false, 'transitioned' => ! $wasVerified && $effectiveStatus === 'verified'];
        });
    }

    private function paymentStatus(array $payload): string
    {
        $status = strtoupper((string) (data_get($payload, 'orderStatus') ?: data_get($payload, 'transactions.0.status')
            ?: data_get($payload, 'transactions.0.detailedStatus')));
        return match ($status) {
            'PAID', 'ACCEPTED', 'CAPTURED', 'AUTHORISED', 'AUTHORIZED' => 'verified',
            'REFUSED', 'CANCELLED', 'CANCELED', 'FAILED', 'ERROR', 'UNPAID' => 'rejected',
            default => 'pending',
        };
    }

    /** @return array{0:string,1:string} */
    private function signatureKey(string $hashKey): array
    {
        return match (strtolower($hashKey)) {
            'sha256_hmac', 'hmac-sha-256' => ['hmac_key', (string) config('services.izipay.hmac_key')],
            'password' => ['password', (string) config('services.izipay.rest_api_key')],
            default => ['unknown', ''],
        };
    }

    private function sanitize(array $payload): array
    {
        $sensitive = ['card', 'cardnumber', 'pan', 'cvv', 'formtoken', 'expirydate', 'expirationdate', 'token'];
        foreach ($payload as $key => $value) {
            $normalized = strtolower(str_replace(['-', '_'], '', (string) $key));
            if (in_array($normalized, $sensitive, true)) {
                unset($payload[$key]);
            } elseif (is_array($value)) {
                $payload[$key] = $this->sanitize($value);
            }
        }
        return $payload;
    }

    private function usesIzipay(Order $order): bool
    {
        return $order->payment_gateway === 'izipay' || $order->payment_method === 'izipay';
    }

    private function decimalToCents(string $amount): int
    {
        $normalized = str_replace(',', '.', trim($amount));
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            return 0;
        }
        [$whole, $decimal] = array_pad(explode('.', $normalized, 2), 2, '');
        return ((int) $whole * 100) + (int) str_pad($decimal, 2, '0');
    }
}
