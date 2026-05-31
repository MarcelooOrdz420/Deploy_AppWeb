<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class SimplePdfReceiptService
{
    public function generate(Order $order): string
    {
        $order->loadMissing('items');

        $lines = $this->buildLines($order);
        $content = $this->buildContentStream($lines);

        return $this->buildPdf($content);
    }

    private function buildLines(Order $order): array
    {
        $createdAt = optional($order->created_at)->format('d/m/Y h:i a') ?: 'n/a';
        $delivery = $order->delivery_type === 'delivery' ? 'Delivery' : 'Recojo en local';
        $paymentMethod = match ($order->payment_method) {
            'yape' => 'Yape',
            'plin' => 'Plin',
            'transfer' => 'Transferencia',
            'culqi' => 'Culqi',
            'cod' => 'Contraentrega',
            default => (string) $order->payment_method,
        };
        $paymentStatus = match ($order->payment_status) {
            'pending' => 'Pendiente',
            'reported' => 'Reportado',
            'verified' => 'Verificado',
            'rejected' => 'Rechazado',
            default => (string) $order->payment_status,
        };

        $lines = [
            ['font' => 'F1', 'size' => 19, 'x' => 50, 'y' => 800, 'text' => 'Pollos y Parrillas El Dorado'],
            ['font' => 'F1', 'size' => 12, 'x' => 50, 'y' => 780, 'text' => 'Boleta de compra'],
            ['font' => 'F1', 'size' => 11, 'x' => 50, 'y' => 760, 'text' => 'Codigo: '.$order->tracking_code],
            ['font' => 'F1', 'size' => 11, 'x' => 330, 'y' => 760, 'text' => 'Fecha: '.$createdAt],
            ['font' => 'F1', 'size' => 11, 'x' => 50, 'y' => 738, 'text' => 'Cliente: '.$this->normalize($order->billing_name ?: $order->customer_name)],
            ['font' => 'F1', 'size' => 11, 'x' => 50, 'y' => 722, 'text' => 'Telefono: '.$this->normalize($order->customer_phone ?: 'n/a')],
            ['font' => 'F1', 'size' => 11, 'x' => 330, 'y' => 722, 'text' => 'DNI: '.$this->normalize($order->billing_document_number ?: 'n/a')],
            ['font' => 'F1', 'size' => 11, 'x' => 50, 'y' => 700, 'text' => 'Entrega: '.$delivery],
            ['font' => 'F1', 'size' => 11, 'x' => 330, 'y' => 700, 'text' => 'Pago: '.$paymentMethod.' / '.$paymentStatus],
            ['font' => 'F1', 'size' => 11, 'x' => 50, 'y' => 678, 'text' => 'Operacion: '.$this->normalize($order->payment_reference ?: 'sin codigo')],
            ['font' => 'F2', 'size' => 10, 'x' => 50, 'y' => 648, 'text' => 'Producto                               Cant   P.Unit    Subtotal'],
            ['font' => 'F2', 'size' => 10, 'x' => 50, 'y' => 636, 'text' => '-----------------------------------------------------------------------'],
        ];

        $y = 620;
        foreach ($order->items as $item) {
            $product = Str::limit($this->normalize($item->product_name), 34, '');
            $qty = str_pad((string) ((int) $item->quantity), 4, ' ', STR_PAD_LEFT);
            $unit = str_pad(number_format((float) $item->unit_price, 2), 8, ' ', STR_PAD_LEFT);
            $total = str_pad(number_format((float) $item->line_total, 2), 9, ' ', STR_PAD_LEFT);
            $row = sprintf('%-34s %4s %8s %9s', $product, $qty, $unit, $total);
            $lines[] = ['font' => 'F2', 'size' => 10, 'x' => 50, 'y' => $y, 'text' => $row];
            $y -= 14;
        }

        $lines[] = ['font' => 'F2', 'size' => 10, 'x' => 50, 'y' => $y - 2, 'text' => '-----------------------------------------------------------------------'];
        $lines[] = ['font' => 'F1', 'size' => 14, 'x' => 360, 'y' => $y - 24, 'text' => 'Total: S/ '.number_format((float) $order->total_amount, 2)];
        $lines[] = ['font' => 'F1', 'size' => 10, 'x' => 50, 'y' => $y - 58, 'text' => 'Gracias por tu compra. Documento generado por el sistema.'];

        return $lines;
    }

    private function buildContentStream(array $lines): string
    {
        $parts = [];

        foreach ($lines as $line) {
            $parts[] = sprintf(
                "BT /%s %s Tf 1 0 0 1 %d %d Tm (%s) Tj ET",
                $line['font'],
                number_format((float) $line['size'], 0, '.', ''),
                $line['x'],
                $line['y'],
                $this->escapePdfText($line['text'])
            );
        }

        return implode("\n", $parts)."\n";
    }

    private function buildPdf(string $content): string
    {
        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>\nendobj\n";
        $objects[] = "6 0 obj\n<< /Length ".strlen($content)." >>\nstream\n".$content."endstream\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    private function normalize(?string $text): string
    {
        return trim(Str::ascii((string) $text));
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\(', '\)', '', ' '],
            $this->normalize($text)
        );
    }
}
