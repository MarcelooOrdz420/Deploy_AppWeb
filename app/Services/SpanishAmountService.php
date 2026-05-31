<?php

namespace App\Services;

class SpanishAmountService
{
    public function toLegend(float $amount, string $currency = 'PEN'): string
    {
        $integer = (int) floor($amount);
        $decimals = (int) round(($amount - $integer) * 100);
        $currencyLabel = $currency === 'USD' ? 'DOLARES' : 'SOLES';

        return sprintf(
            'SON %s CON %02d/100 %s',
            $this->integerToWords($integer),
            $decimals,
            $currencyLabel
        );
    }

    private function integerToWords(int $number): string
    {
        if ($number === 0) {
            return 'CERO';
        }

        $units = [
            '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
            'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE',
            'DIECIOCHO', 'DIECINUEVE', 'VEINTE', 'VEINTIUNO', 'VEINTIDOS', 'VEINTITRES',
            'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE',
        ];
        $tens = ['', '', '', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $hundreds = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($number === 100) {
            return 'CIEN';
        }

        if ($number < 30) {
            return $units[$number];
        }

        if ($number < 100) {
            $ten = intdiv($number, 10);
            $unit = $number % 10;
            return $unit === 0 ? $tens[$ten] : $tens[$ten].' Y '.$units[$unit];
        }

        if ($number < 1000) {
            $hundred = intdiv($number, 100);
            $remainder = $number % 100;
            return trim($hundreds[$hundred].' '.$this->integerToWords($remainder));
        }

        if ($number < 1000000) {
            $thousands = intdiv($number, 1000);
            $remainder = $number % 1000;
            $prefix = $thousands === 1 ? 'MIL' : $this->integerToWords($thousands).' MIL';
            return trim($prefix.' '.$this->integerToWords($remainder));
        }

        $millions = intdiv($number, 1000000);
        $remainder = $number % 1000000;
        $prefix = $millions === 1 ? 'UN MILLON' : $this->integerToWords($millions).' MILLONES';

        return trim($prefix.' '.$this->integerToWords($remainder));
    }
}
