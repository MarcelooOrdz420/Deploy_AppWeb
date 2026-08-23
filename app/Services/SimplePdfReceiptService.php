<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class SimplePdfReceiptService
{
    // Marca de la tienda en RGB 0-1 (aprox. #FF6F1F, #25170F).
    private const ORANGE = [1.0, 0.435, 0.106];
    private const DARK = [0.145, 0.09, 0.055];
    private const MUTED = [0.4, 0.36, 0.32];
    private const WHITE = [1.0, 1.0, 1.0];
    private const BAND = [1.0, 0.965, 0.937];

    public function generate(Order $order): string
    {
        $order->loadMissing('items');

        $rects = $this->buildRects($order);
        $lines = $this->buildLines($order);
        $content = $this->buildContentStream($rects, $lines);

        return $this->buildPdf($content);
    }

    private function buildRects(Order $order): array
    {
        $rects = [
            // Banda superior con el nombre de la tienda.
            ['x' => 40, 'y' => 748, 'w' => 515, 'h' => 56, 'color' => self::ORANGE],
            // Franja suave detras del encabezado de la tabla de productos.
            ['x' => 40, 'y' => 604, 'w' => 515, 'h' => 18, 'color' => self::BAND],
        ];

        $itemsCount = max(1, $order->items->count());
        $tableBottom = 604 - ($itemsCount * 16) - 10;

        // Caja del total, siempre justo debajo de la tabla de productos.
        $rects[] = ['x' => 340, 'y' => $tableBottom - 34, 'w' => 215, 'h' => 30, 'color' => self::BAND];

        return $rects;
    }

    private function buildLines(Order $order): array
    {
        $createdAt = optional($order->created_at)->format('d/m/Y h:i a') ?: 'n/a';
        $delivery = $order->delivery_type === 'delivery' ? 'Delivery' : 'Recojo en local';
        $paymentMethod = match ($order->payment_method) {
            'izipay' => 'Tarjeta (Izipay)',
            'tarjeta' => 'Tarjeta',
            'yape' => 'Yape',
            'cod' => 'Contraentrega',
            'efectivo' => 'Efectivo',
            default => (string) $order->payment_method,
        };
        $receiptLabel = match ($order->billing_receipt_type) {
            'factura' => 'Factura de compra',
            'boleta' => 'Boleta de compra',
            default => 'Comprobante de compra',
        };
        $documentLabel = $order->billing_document_type === 'ruc' ? 'RUC' : 'DNI';

        $lines = [
            // Banda superior (texto blanco sobre el rectangulo naranja).
            ['font' => 'F3', 'size' => 19, 'x' => 54, 'y' => 784, 'text' => 'Pollos y Parrillas El Dorado', 'color' => self::WHITE],
            ['font' => 'F1', 'size' => 10.5, 'x' => 54, 'y' => 762, 'text' => $receiptLabel.' - '.$order->tracking_code, 'color' => self::WHITE],
            ['font' => 'F1', 'size' => 10.5, 'x' => 380, 'y' => 762, 'text' => 'Fecha: '.$createdAt, 'color' => self::WHITE],

            // Datos de la compra.
            ['font' => 'F3', 'size' => 10.5, 'x' => 54, 'y' => 718, 'text' => 'Cliente:', 'color' => self::MUTED],
            ['font' => 'F1', 'size' => 11, 'x' => 108, 'y' => 718, 'text' => $this->normalize($order->billing_name ?: $order->customer_name), 'color' => self::DARK],
            ['font' => 'F3', 'size' => 10.5, 'x' => 330, 'y' => 718, 'text' => $documentLabel.':', 'color' => self::MUTED],
            ['font' => 'F1', 'size' => 11, 'x' => 372, 'y' => 718, 'text' => $this->normalize($order->billing_document_number ?: 'n/a'), 'color' => self::DARK],

            ['font' => 'F3', 'size' => 10.5, 'x' => 54, 'y' => 698, 'text' => 'Entrega:', 'color' => self::MUTED],
            ['font' => 'F1', 'size' => 11, 'x' => 108, 'y' => 698, 'text' => $delivery, 'color' => self::DARK],
            ['font' => 'F3', 'size' => 10.5, 'x' => 330, 'y' => 698, 'text' => 'Pago:', 'color' => self::MUTED],
            ['font' => 'F1', 'size' => 11, 'x' => 372, 'y' => 698, 'text' => $paymentMethod, 'color' => self::DARK],

            ['font' => 'F3', 'size' => 10.5, 'x' => 54, 'y' => 678, 'text' => 'Operacion:', 'color' => self::MUTED],
            ['font' => 'F1', 'size' => 11, 'x' => 128, 'y' => 678, 'text' => $this->normalize($order->payment_reference ?: 'sin codigo'), 'color' => self::DARK],

            // Encabezado de la tabla (sobre la franja clara).
            ['font' => 'F3', 'size' => 10, 'x' => 54, 'y' => 610, 'text' => 'Producto', 'color' => self::MUTED],
            ['font' => 'F3', 'size' => 10, 'x' => 388, 'y' => 610, 'text' => 'Cant.', 'color' => self::MUTED],
            ['font' => 'F3', 'size' => 10, 'x' => 432, 'y' => 610, 'text' => 'P. Unit.', 'color' => self::MUTED],
            ['font' => 'F3', 'size' => 10, 'x' => 493, 'y' => 610, 'text' => 'Subtotal', 'color' => self::MUTED],
        ];

        $y = 588;
        foreach ($order->items as $item) {
            $product = Str::limit($this->normalize($item->product_name), 44, '');
            $lines[] = ['font' => 'F1', 'size' => 10.5, 'x' => 54, 'y' => $y, 'text' => $product, 'color' => self::DARK];
            $lines[] = ['font' => 'F1', 'size' => 10.5, 'x' => 388, 'y' => $y, 'text' => (string) ((int) $item->quantity), 'color' => self::DARK];
            $lines[] = ['font' => 'F1', 'size' => 10.5, 'x' => 428, 'y' => $y, 'text' => 'S/ '.number_format((float) $item->unit_price, 2), 'color' => self::DARK];
            $lines[] = ['font' => 'F1', 'size' => 10.5, 'x' => 490, 'y' => $y, 'text' => 'S/ '.number_format((float) $item->line_total, 2), 'color' => self::DARK];
            $y -= 16;
        }

        $itemsCount = max(1, $order->items->count());
        $tableBottom = 604 - ($itemsCount * 16) - 10;

        $lines[] = ['font' => 'F3', 'size' => 10.5, 'x' => 354, 'y' => $tableBottom - 15, 'text' => 'Total a pagar', 'color' => self::MUTED];
        $lines[] = ['font' => 'F3', 'size' => 16, 'x' => 460, 'y' => $tableBottom - 17, 'text' => 'S/ '.number_format((float) $order->total_amount, 2), 'color' => self::ORANGE];

        $lines[] = ['font' => 'F1', 'size' => 9, 'x' => 54, 'y' => $tableBottom - 58, 'text' => 'Gracias por tu compra. Documento generado por el sistema, no reemplaza boleta/factura electronica salvo que se indique.', 'color' => self::MUTED];

        return $lines;
    }

    private function buildContentStream(array $rects, array $lines): string
    {
        $parts = [];

        foreach ($rects as $rect) {
            [$r, $g, $b] = $rect['color'];
            $parts[] = sprintf(
                '%s %s %s rg %d %d %d %d re f',
                $this->fmt($r),
                $this->fmt($g),
                $this->fmt($b),
                $rect['x'],
                $rect['y'],
                $rect['w'],
                $rect['h']
            );
        }

        foreach ($lines as $line) {
            [$r, $g, $b] = $line['color'] ?? self::DARK;
            $parts[] = sprintf(
                '%s %s %s rg BT /%s %s Tf 1 0 0 1 %d %d Tm (%s) Tj ET',
                $this->fmt($r),
                $this->fmt($g),
                $this->fmt($b),
                $line['font'],
                number_format((float) $line['size'], 1, '.', ''),
                $line['x'],
                $line['y'],
                $this->escapePdfText($line['text'])
            );
        }

        return implode("\n", $parts)."\n";
    }

    private function fmt(float $value): string
    {
        return number_format($value, 3, '.', '');
    }

    private function buildPdf(string $content): string
    {
        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R /F3 7 0 R >> >> /Contents 6 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>\nendobj\n";
        $objects[] = "6 0 obj\n<< /Length ".strlen($content)." >>\nstream\n".$content."endstream\nendobj\n";
        $objects[] = "7 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n";

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
