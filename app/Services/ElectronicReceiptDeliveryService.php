<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Mail\ResendEmailService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class ElectronicReceiptDeliveryService
{
    public function __construct(
        private readonly ElectronicInvoiceService $invoiceService,
        private readonly ResendEmailService $resendEmailService,
    ) {}

    public function issueAfterVerifiedPayment(Order $order): array
    {
        $order->loadMissing(['items', 'user']);

        if ((string) $order->payment_status !== 'verified'
            || ! in_array((string) $order->payment_method, ['izipay', 'yape', 'cod'], true)
            || ((string) $order->payment_method === 'cod' && (string) $order->status !== Order::STATUS_DELIVERED)
            || ! in_array((string) $order->billing_receipt_type, ['boleta', 'factura'], true)
            || ! (bool) config('einvoice.auto_send', false)) {
            return ['attempted' => false];
        }

        try {
            $response = $this->invoiceService->sendIfEligible($order);
            if (isset($response['reason']) || (($response['eligible'] ?? true) === false)) {
                return ['attempted' => false, 'reason' => $response['reason'] ?? 'not_eligible'];
            }
            $fresh = $order->fresh(['items', 'user']);
            $this->storeDeliveryStatus($fresh, [
                'status' => 'requested',
                'mode' => 'provider',
                'recipient' => $this->recipient($fresh),
                'requested_at' => now()->toIso8601String(),
                'last_error' => null,
            ]);

            Log::info('Electronic receipt automatically issued after verified payment.', [
                'order_id' => $order->id,
                'provider' => config('einvoice.provider'),
            ]);

            return ['attempted' => true, 'ok' => true, 'response' => $response];
        } catch (Throwable $exception) {
            $this->storeDeliveryStatus($order->fresh(), [
                'status' => 'failed',
                'mode' => 'provider',
                'recipient' => $this->recipient($order),
                'failed_at' => now()->toIso8601String(),
                'last_error' => $exception->getMessage(),
            ]);
            Log::error('Automatic electronic receipt issue failed.', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
            report($exception);

            return ['attempted' => true, 'ok' => false, 'error' => $exception->getMessage()];
        }
    }

    public function sendCustomerCopy(Order $order): array
    {
        $order->loadMissing(['items', 'user']);

        if ((string) $order->payment_status !== 'verified') {
            throw new RuntimeException('El pago debe estar verificado antes de enviar el comprobante.');
        }
        if (! in_array((string) $order->billing_receipt_type, ['boleta', 'factura'], true)) {
            throw new RuntimeException('El pedido no tiene boleta o factura seleccionada.');
        }

        $to = $this->recipient($order);
        if ($to === '') {
            throw new RuntimeException('El pedido no tiene un correo de cliente disponible.');
        }

        // This is idempotent: an already issued invoice is returned without creating another one.
        $this->invoiceService->sendInvoice($order);
        $order = $order->fresh(['items', 'user']);

        $type = $order->billing_receipt_type === 'factura' ? 'Factura' : 'Boleta';
        $tracking = (string) ($order->tracking_code ?: $order->id);
        $subject = "{$type} de tu pedido {$tracking}";
        $pdfUrl = $this->providerPdfUrl($order);
        if ($pdfUrl === '') {
            throw new RuntimeException('Nubefact no devolvio el enlace del PDF oficial. Revisa el estado de emision del pedido.');
        }
        $safeName = e((string) ($order->billing_name ?: $order->customer_name ?: 'cliente'));
        $safeUrl = e($pdfUrl);
        $link = $pdfUrl !== ''
            ? '<p><a href="'.$safeUrl.'" style="display:inline-block;padding:12px 20px;background:#f97316;color:#fff;text-decoration:none;border-radius:999px;font-weight:700;">Abrir comprobante electrónico</a></p>'
            : '';
        $html = '<div style="font-family:Arial,sans-serif;color:#111827;padding:24px;">'
            .'<h1 style="font-size:24px;">Tu comprobante está listo</h1>'
            .'<p>Hola '.$safeName.', adjuntamos el comprobante del pedido <strong>'.e($tracking).'</strong>.</p>'
            .$link.'<p>Gracias por tu compra.</p></div>';
        $text = "Hola {$order->billing_name}, tu {$type} del pedido {$tracking} esta lista."
            .($pdfUrl !== '' ? "\n\nAbrir comprobante: {$pdfUrl}" : '');
        $filename = strtolower($type).'-'.$tracking.'.pdf';
        $pdf = $this->downloadOfficialPdf($pdfUrl);

        try {
            if ($this->resendEmailService->enabled()) {
                $this->resendEmailService->send([
                    'to' => [$to],
                    'subject' => $subject,
                    'html' => $html,
                    'text' => $text,
                    'attachments' => [[
                        'filename' => $filename,
                        'content' => base64_encode($pdf),
                    ]],
                ]);
            } else {
                Mail::html($html, function ($message) use ($to, $subject, $pdf, $filename): void {
                    $message->to($to)->subject($subject)->attachData($pdf, $filename, [
                        'mime' => 'application/pdf',
                    ]);
                });
            }

            $this->storeDeliveryStatus($order, [
                'status' => 'sent',
                'mode' => 'admin_retry',
                'recipient' => $to,
                'sent_at' => now()->toIso8601String(),
                'last_error' => null,
            ]);

            return ['ok' => true, 'recipient' => $to, 'message' => 'Comprobante enviado al cliente.'];
        } catch (Throwable $exception) {
            $this->storeDeliveryStatus($order, [
                'status' => 'failed',
                'mode' => 'admin_retry',
                'recipient' => $to,
                'failed_at' => now()->toIso8601String(),
                'last_error' => $exception->getMessage(),
            ]);
            throw new RuntimeException('No se pudo enviar el correo: '.$exception->getMessage(), previous: $exception);
        }
    }

    private function recipient(Order $order): string
    {
        return trim((string) ($order->billing_email ?: $order->customer_email ?: $order->user?->email));
    }

    public function providerPdfUrl(Order $order): string
    {
        foreach (['enlace_del_pdf', 'pdf', 'pdf_url', 'links.pdf', 'linkPdf'] as $key) {
            $value = trim((string) data_get($order->billing_metadata, 'einvoice.response.'.$key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    public function downloadOfficialPdf(string $url): string
    {
        try {
            $response = Http::timeout(20)->accept('application/pdf')->get($url);
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo descargar el PDF oficial de Nubefact.', previous: $exception);
        }

        $content = $response->body();
        if (! $response->successful() || $content === '' || ! str_starts_with($content, '%PDF')) {
            throw new RuntimeException('Nubefact no devolvio un PDF oficial valido.');
        }

        return $content;
    }

    private function storeDeliveryStatus(Order $order, array $status): void
    {
        $metadata = $order->billing_metadata ?? [];
        $previous = data_get($metadata, 'einvoice.delivery', []);
        data_set($metadata, 'einvoice.delivery', array_merge(is_array($previous) ? $previous : [], $status));
        $order->update(['billing_metadata' => $metadata]);
    }
}
