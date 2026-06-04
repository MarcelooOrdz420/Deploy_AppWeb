<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashClosure;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AdminCashClosureController extends Controller
{
    public function index(): JsonResponse
    {
        if (! Schema::hasTable('cash_closures')) {
            return response()->json([]);
        }

        $closures = CashClosure::query()
            ->with('closer:id,name,email')
            ->latest('business_date')
            ->latest('id')
            ->limit(20)
            ->get();

        return response()->json($closures);
    }

    public function summary(Request $request): JsonResponse
    {
        $businessDate = $this->resolveBusinessDate($request->query('date'));

        return response()->json([
            ...$this->buildSummary($businessDate),
            'policy' => $this->buildClosingPolicy($businessDate),
        ]);
    }

    public function export(Request $request): Response
    {
        if (! Schema::hasTable('cash_closures')) {
            return response($this->buildCashClosuresExcelHtml([], 0), 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="cierres-caja-admin.xls"',
            ]);
        }

        $rows = CashClosure::query()
            ->with('closer:id,name,email')
            ->latest('business_date')
            ->latest('id')
            ->get()
            ->map(function (CashClosure $closure): array {
                return [
                    $closure->business_date,
                    (string) $closure->orders_count,
                    number_format((float) $closure->gross_sales, 2, '.', ''),
                    number_format((float) $closure->verified_sales, 2, '.', ''),
                    number_format((float) $closure->cash_sales, 2, '.', ''),
                    number_format((float) $closure->digital_sales, 2, '.', ''),
                    number_format((float) $closure->declared_cash, 2, '.', ''),
                    number_format((float) $closure->expected_cash, 2, '.', ''),
                    number_format((float) $closure->difference_amount, 2, '.', ''),
                    $closure->closer?->name ?: 'Sistema',
                    optional($closure->closed_at)?->setTimezone($this->timezone())->format('Y-m-d H:i:s') ?: '',
                    $closure->notes ?: '',
                ];
            })
            ->all();

        return response($this->buildCashClosuresExcelHtml($rows, count($rows)), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cierres-caja-admin.xls"',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! Schema::hasTable('cash_closures')) {
            return response()->json([
                'message' => 'La tabla cash_closures aun no existe en la base de datos. Ejecuta las migraciones antes de guardar cierres.',
                'requires_migration' => true,
            ], 409);
        }

        $data = $request->validate([
            'business_date' => ['nullable', 'date_format:Y-m-d'],
            'declared_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $businessDate = $this->resolveBusinessDate($data['business_date'] ?? null);
        $policy = $this->buildClosingPolicy($businessDate);

        if (! $policy['can_close']) {
            return response()->json([
                'message' => $policy['message'],
                'policy' => $policy,
            ], 422);
        }

        $existingClosure = CashClosure::query()
            ->where('business_date', $businessDate)
            ->first();

        if ($existingClosure) {
            return response()->json([
                'message' => 'Ya existe un cierre de caja registrado para esta fecha operativa.',
                'policy' => $policy,
                'closure' => $existingClosure->load('closer:id,name,email'),
            ], 409);
        }

        $summary = $this->buildSummary($businessDate);
        $declaredCash = round((float) $data['declared_cash'], 2);
        $expectedCash = round((float) $summary['totals']['cash_sales'], 2);

        $closure = CashClosure::query()->create([
            'business_date' => $businessDate,
            'orders_count' => (int) $summary['totals']['orders_count'],
            'gross_sales' => (float) $summary['totals']['gross_sales'],
            'verified_sales' => (float) $summary['totals']['verified_sales'],
            'cash_sales' => $expectedCash,
            'digital_sales' => (float) $summary['totals']['digital_sales'],
            'declared_cash' => $declaredCash,
            'expected_cash' => $expectedCash,
            'difference_amount' => round($declaredCash - $expectedCash, 2),
            'notes' => $data['notes'] ?? null,
            'summary_payload' => $summary,
            'closed_by' => $request->user()?->id,
            'closed_at' => now($this->timezone()),
        ]);

        return response()->json($closure->load('closer:id,name,email'));
    }

    private function resolveBusinessDate(?string $date): string
    {
        return $date
            ? Carbon::createFromFormat('Y-m-d', $date, $this->timezone())->toDateString()
            : now($this->timezone())->toDateString();
    }

    private function buildSummary(string $businessDate): array
    {
        $orders = Order::query()
            ->whereDate('created_at', $businessDate)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->get([
                'id',
                'tracking_code',
                'customer_name',
                'status',
                'payment_method',
                'payment_status',
                'total_amount',
                'created_at',
            ]);

        $grossSales = round((float) $orders->sum('total_amount'), 2);
        $cashSales = round((float) $orders->where('payment_method', 'cod')->sum('total_amount'), 2);
        $digitalSales = round((float) $orders->where('payment_method', '!=', 'cod')->sum('total_amount'), 2);
        $verifiedSales = round((float) $orders
            ->filter(fn (Order $order): bool => $order->payment_method === 'cod' || $order->payment_status === 'verified')
            ->sum('total_amount'), 2);

        $paymentBreakdown = $orders
            ->groupBy(fn (Order $order): string => (string) $order->payment_method)
            ->map(fn ($group, string $method): array => [
                'method' => $method,
                'orders_count' => $group->count(),
                'total' => round((float) $group->sum('total_amount'), 2),
            ])
            ->values()
            ->all();

        return [
            'business_date' => $businessDate,
            'totals' => [
                'orders_count' => $orders->count(),
                'gross_sales' => $grossSales,
                'verified_sales' => $verifiedSales,
                'cash_sales' => $cashSales,
                'digital_sales' => $digitalSales,
            ],
            'payments' => $paymentBreakdown,
        ];
    }

    private function buildClosingPolicy(string $businessDate): array
    {
        $now = now($this->timezone());
        $today = $now->toDateString();
        $cutoffAt = Carbon::createFromFormat('Y-m-d H:i:s', $businessDate.' 23:00:00', $this->timezone());
        $existingClosure = Schema::hasTable('cash_closures')
            ? CashClosure::query()->where('business_date', $businessDate)->exists()
            : false;

        if ($existingClosure) {
            return [
                'timezone' => $this->timezone(),
                'cutoff_hour' => '23:00',
                'can_close' => false,
                'already_closed' => true,
                'message' => 'Esta fecha ya tiene un cierre de caja registrado.',
            ];
        }

        if ($businessDate !== $today) {
            return [
                'timezone' => $this->timezone(),
                'cutoff_hour' => '23:00',
                'can_close' => false,
                'already_closed' => false,
                'message' => 'Solo puedes registrar el cierre para la fecha operativa actual de Lima.',
            ];
        }

        if ($now->lt($cutoffAt)) {
            return [
                'timezone' => $this->timezone(),
                'cutoff_hour' => '23:00',
                'can_close' => false,
                'already_closed' => false,
                'message' => 'El cierre de caja solo se habilita desde las 11:00 PM hora Lima.',
            ];
        }

        return [
            'timezone' => $this->timezone(),
            'cutoff_hour' => '23:00',
            'can_close' => true,
            'already_closed' => false,
            'message' => 'Ya puedes registrar el cierre de caja de hoy.',
        ];
    }

    private function buildCashClosuresExcelHtml(array $rows, int $closuresCount): string
    {
        $header = [
            'Fecha operativa',
            'Pedidos',
            'Venta bruta',
            'Ventas verificadas',
            'Efectivo esperado',
            'Pagos digitales',
            'Efectivo declarado',
            'Efectivo esperado final',
            'Diferencia',
            'Cerrado por',
            'Fecha cierre',
            'Observaciones',
        ];

        $title = 'REPORTE DE CIERRES DE CAJA';
        $generatedAt = now($this->timezone())->format('Y-m-d H:i:s');
        $colspan = count($header);
        $headHtml = implode('', array_map(
            fn (string $cell): string => '<th>'.$this->excelCell($cell).'</th>',
            $header
        ));

        $rowsHtml = implode('', array_map(function (array $row): string {
            $cells = implode('', array_map(
                fn (string $cell): string => '<td>'.$this->excelCell($cell).'</td>',
                $row
            ));

            return '<tr>'.$cells.'</tr>';
        }, $rows));

        if ($rowsHtml === '') {
            $rowsHtml = '<tr><td colspan="'.$colspan.'" class="empty-row">No hay cierres de caja registrados para exportar.</td></tr>';
        }

        return <<<HTML
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Calibri, Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d7b08a; padding: 8px 10px; font-size: 12px; vertical-align: top; }
        .report-title { background: #f28c18; color: #111111; font-weight: 700; font-size: 16px; text-align: left; }
        .report-meta { background: #fff3e4; color: #3d2a1e; font-weight: 700; }
        thead th { background: #f28c18; color: #111111; font-weight: 700; text-align: left; }
        tbody tr:nth-child(even) td { background: #fff7ef; }
        tbody tr:nth-child(odd) td { background: #ffffff; }
        .empty-row { text-align: center; font-weight: 700; color: #7b5c45; }
    </style>
</head>
<body>
    <table>
        <tr><td colspan="{$colspan}" class="report-title">{$this->excelCell($title)}</td></tr>
        <tr><td colspan="{$colspan}" class="report-meta">Generado: {$this->excelCell($generatedAt)} | Registros: {$closuresCount} | Zona horaria: {$this->excelCell($this->timezone())}</td></tr>
        <thead>
            <tr>{$headHtml}</tr>
        </thead>
        <tbody>
            {$rowsHtml}
        </tbody>
    </table>
</body>
</html>
HTML;
    }

    private function excelCell(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function timezone(): string
    {
        return 'America/Lima';
    }
}
