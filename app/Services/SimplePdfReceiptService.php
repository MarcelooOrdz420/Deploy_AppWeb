<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Genera un comprobante simple en formato ticket angosto (estilo impresora
 * termica de mostrador), con borde y secciones separadas por lineas
 * punteadas. Es un PDF armado a mano (sin libreria externa), por lo que el
 * ancho de texto se estima con un factor promedio por caracter en vez de
 * metricas reales de fuente: alcanza para centrar/ajustar lineas de forma
 * razonable, no es una medicion exacta.
 */
class SimplePdfReceiptService
{
    private const PAGE_WIDTH = 226.0; // ~80mm, ancho tipico de ticket.
    private const MARGIN_X = 14.0;
    private const MARGIN_TOP = 22.0;
    private const MARGIN_BOTTOM = 22.0;
    private const CONTENT_WIDTH = self::PAGE_WIDTH - (self::MARGIN_X * 2);

    private const DARK = [0.145, 0.09, 0.055];
    private const MUTED = [0.45, 0.4, 0.36];
    private const ORANGE = [0.87, 0.34, 0.02];
    private const LINE = [0.75, 0.7, 0.65];

    /** @var list<array{type:string, height:float, ...}> */
    private array $rows = [];

    public function generate(Order $order): string
    {
        $order->loadMissing('items');
        $this->rows = [];

        $this->buildHeader($order);
        $this->buildInfo($order);
        $this->buildItems($order);
        $this->buildTotal($order);
        $this->buildFooter();

        $contentHeight = array_sum(array_column($this->rows, 'height'));
        $pageHeight = max(300.0, self::MARGIN_TOP + $contentHeight + self::MARGIN_BOTTOM);

        $content = $this->renderContentStream($pageHeight);

        return $this->buildPdf($content, $pageHeight);
    }

    private function buildHeader(Order $order): void
    {
        $receiptLabel = match ($order->billing_receipt_type) {
            'factura' => 'FACTURA DE COMPRA',
            'boleta' => 'BOLETA DE COMPRA',
            default => 'COMPROBANTE DE COMPRA',
        };

        $this->addGap(4);
        foreach (['POLLOS Y PARRILLAS', 'EL DORADO'] as $line) {
            $this->addCenteredText($line, 13, bold: true, height: 17, color: self::DARK);
        }
        $this->addGap(3);
        $this->addCenteredText($receiptLabel, 8.5, bold: false, height: 12, color: self::MUTED);
        $this->addCenteredText($order->tracking_code, 9, bold: true, height: 13, color: self::ORANGE);
        $this->addGap(6);
        $this->addDashedDivider();
        $this->addGap(6);
    }

    private function buildInfo(Order $order): void
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
        $documentLabel = $order->billing_document_type === 'ruc' ? 'RUC' : 'DNI';

        $rows = [
            ['Fecha', $createdAt],
            ['Cliente', $this->normalize($order->billing_name ?: $order->customer_name)],
            ['Entrega', $delivery],
            ['Pago', $paymentMethod],
        ];

        if (! empty($order->billing_document_number)) {
            $rows[] = [$documentLabel, $this->normalize((string) $order->billing_document_number)];
        }

        if (! empty($order->payment_reference)) {
            $rows[] = ['Operacion', $this->normalize((string) $order->payment_reference)];
        }

        foreach ($rows as [$label, $value]) {
            $this->addLabelValueRow($label, $value);
        }

        $this->addGap(6);
        $this->addDashedDivider();
        $this->addGap(6);
    }

    private function buildItems(Order $order): void
    {
        $this->addText('PRODUCTOS', 8.5, self::MARGIN_X, bold: true, height: 13, color: self::MUTED);
        $this->addGap(3);

        foreach ($order->items as $item) {
            $name = $this->wrapText($this->normalize($item->product_name), self::CONTENT_WIDTH, 9.5, true);
            foreach ($name as $line) {
                $this->addText($line, 9.5, self::MARGIN_X, bold: true, height: 13, color: self::DARK);
            }

            $detail = sprintf(
                '%d x S/ %s',
                (int) $item->quantity,
                number_format((float) $item->unit_price, 2)
            );
            $subtotal = 'S/ '.number_format((float) $item->line_total, 2);
            $this->addTwoColumnRow($detail, $subtotal, 9, self::MUTED, self::DARK, height: 14);
            $this->addGap(4);
        }

        $this->addGap(2);
        $this->addDashedDivider();
        $this->addGap(6);
    }

    private function buildTotal(Order $order): void
    {
        $amount = 'S/ '.number_format((float) $order->total_amount, 2);
        $this->rows[] = [
            'type' => 'total_box',
            'height' => 34.0,
            'label' => 'TOTAL A PAGAR',
            'value' => $amount,
        ];
        $this->addGap(10);
    }

    private function buildFooter(): void
    {
        $note = 'Gracias por tu compra. Documento generado por el sistema; no reemplaza boleta o factura electronica salvo que se indique arriba.';
        $lines = $this->wrapText($note, self::CONTENT_WIDTH, 7.5, false);
        foreach ($lines as $line) {
            $this->addCenteredText($line, 7.5, bold: false, height: 10, color: self::MUTED);
        }
        $this->addGap(4);
    }

    // --- helpers de construccion de filas ---

    private function addGap(float $height): void
    {
        $this->rows[] = ['type' => 'gap', 'height' => $height];
    }

    private function addDashedDivider(): void
    {
        $this->rows[] = ['type' => 'dashed', 'height' => 1.0];
    }

    private function addText(string $text, float $size, float $x, bool $bold, float $height, array $color): void
    {
        $this->rows[] = [
            'type' => 'text', 'height' => $height, 'text' => $text, 'size' => $size,
            'x' => $x, 'bold' => $bold, 'color' => $color,
        ];
    }

    private function addCenteredText(string $text, float $size, bool $bold, float $height, array $color): void
    {
        $this->rows[] = [
            'type' => 'centered_text', 'height' => $height, 'text' => $text, 'size' => $size,
            'bold' => $bold, 'color' => $color,
        ];
    }

    private function addLabelValueRow(string $label, string $value): void
    {
        $this->rows[] = [
            'type' => 'label_value', 'height' => 13.0, 'label' => $label, 'value' => $value,
        ];
    }

    private function addTwoColumnRow(string $left, string $right, float $size, array $leftColor, array $rightColor, float $height): void
    {
        $this->rows[] = [
            'type' => 'two_column', 'height' => $height, 'left' => $left, 'right' => $right,
            'size' => $size, 'leftColor' => $leftColor, 'rightColor' => $rightColor,
        ];
    }

    // --- estimacion de ancho de texto (Helvetica no tiene metricas reales
    // disponibles aqui, se aproxima con un factor promedio por caracter) ---

    private function textWidth(string $text, float $size, bool $bold): float
    {
        $factor = $bold ? 0.60 : 0.52;

        return mb_strlen($text) * $size * $factor;
    }

    private function wrapText(string $text, float $maxWidth, float $size, bool $bold): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;
            if ($this->textWidth($candidate, $size, $bold) > $maxWidth && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines ?: [''];
    }

    // --- render final: convierte las filas (orden logico de arriba hacia
    // abajo) en operadores de PDF con coordenadas absolutas ---

    private function renderContentStream(float $pageHeight): string
    {
        $parts = [];

        // Borde exterior del ticket.
        $parts[] = sprintf(
            '%s %s %s RG 0.8 w %s %s %s %s re S',
            $this->fmt(self::LINE[0]), $this->fmt(self::LINE[1]), $this->fmt(self::LINE[2]),
            $this->fmt(6), $this->fmt(6),
            $this->fmt(self::PAGE_WIDTH - 12), $this->fmt($pageHeight - 12)
        );

        $cursor = $pageHeight - self::MARGIN_TOP;

        foreach ($this->rows as $row) {
            $top = $cursor;
            $cursor -= $row['height'];

            switch ($row['type']) {
                case 'gap':
                    break;

                case 'dashed':
                    $y = $top - 0.5;
                    $parts[] = sprintf(
                        '%s %s %s RG 0.6 w [2 2] 0 d %s %s m %s %s l S [] 0 d',
                        $this->fmt(self::LINE[0]), $this->fmt(self::LINE[1]), $this->fmt(self::LINE[2]),
                        $this->fmt(self::MARGIN_X), $this->fmt($y),
                        $this->fmt(self::PAGE_WIDTH - self::MARGIN_X), $this->fmt($y)
                    );
                    break;

                case 'text':
                    $baseline = $top - ($row['height'] * 0.78);
                    $parts[] = $this->textOp($row['text'], $row['size'], $row['x'], $baseline, $row['bold'], $row['color']);
                    break;

                case 'centered_text':
                    $baseline = $top - ($row['height'] * 0.78);
                    $x = self::MARGIN_X + max(0, (self::CONTENT_WIDTH - $this->textWidth($row['text'], $row['size'], $row['bold'])) / 2);
                    $parts[] = $this->textOp($row['text'], $row['size'], $x, $baseline, $row['bold'], $row['color']);
                    break;

                case 'label_value':
                    $baseline = $top - ($row['height'] * 0.78);
                    $parts[] = $this->textOp($row['label'].':', 8.5, self::MARGIN_X, $baseline, true, self::MUTED);
                    $labelWidth = $this->textWidth($row['label'].': ', 8.5, true);
                    $parts[] = $this->textOp($row['value'], 8.5, self::MARGIN_X + $labelWidth, $baseline, false, self::DARK);
                    break;

                case 'two_column':
                    $baseline = $top - ($row['height'] * 0.78);
                    $parts[] = $this->textOp($row['left'], $row['size'], self::MARGIN_X, $baseline, false, $row['leftColor']);
                    $rightWidth = $this->textWidth($row['right'], $row['size'], true);
                    $rightX = self::PAGE_WIDTH - self::MARGIN_X - $rightWidth;
                    $parts[] = $this->textOp($row['right'], $row['size'], $rightX, $baseline, true, $row['rightColor']);
                    break;

                case 'total_box':
                    $boxTop = $top;
                    $boxHeight = $row['height'];
                    $parts[] = sprintf(
                        '%s %s %s rg %s %s %s %s re f',
                        $this->fmt(1.0), $this->fmt(0.965), $this->fmt(0.937),
                        $this->fmt(self::MARGIN_X), $this->fmt($boxTop - $boxHeight),
                        $this->fmt(self::CONTENT_WIDTH), $this->fmt($boxHeight)
                    );
                    $parts[] = sprintf(
                        '%s %s %s RG 0.8 w %s %s %s %s re S',
                        $this->fmt(self::ORANGE[0]), $this->fmt(self::ORANGE[1]), $this->fmt(self::ORANGE[2]),
                        $this->fmt(self::MARGIN_X), $this->fmt($boxTop - $boxHeight),
                        $this->fmt(self::CONTENT_WIDTH), $this->fmt($boxHeight)
                    );
                    $labelBaseline = $boxTop - 13;
                    $valueBaseline = $boxTop - 27;
                    $parts[] = $this->textOp($row['label'], 8.5, self::MARGIN_X + 10, $labelBaseline, true, self::MUTED);
                    $valueWidth = $this->textWidth($row['value'], 15, true);
                    $valueX = self::PAGE_WIDTH - self::MARGIN_X - 10 - $valueWidth;
                    $parts[] = $this->textOp($row['value'], 15, $valueX, $valueBaseline, true, self::ORANGE);
                    break;
            }
        }

        return implode("\n", $parts)."\n";
    }

    private function textOp(string $text, float $size, float $x, float $y, bool $bold, array $color): string
    {
        [$r, $g, $b] = $color;

        return sprintf(
            '%s %s %s rg BT /%s %s Tf 1 0 0 1 %s %s Tm (%s) Tj ET',
            $this->fmt($r), $this->fmt($g), $this->fmt($b),
            $bold ? 'F3' : 'F1',
            number_format($size, 1, '.', ''),
            $this->fmt($x), $this->fmt($y),
            $this->escapePdfText($text)
        );
    }

    private function fmt(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function buildPdf(string $content, float $pageHeight): string
    {
        $width = (int) round(self::PAGE_WIDTH);
        $height = (int) round($pageHeight);

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$width} {$height}] /Resources << /Font << /F1 4 0 R /F3 7 0 R >> >> /Contents 6 0 R >>\nendobj\n";
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
