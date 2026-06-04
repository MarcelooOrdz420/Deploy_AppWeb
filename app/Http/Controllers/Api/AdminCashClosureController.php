<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashClosure;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminCashClosureController extends Controller
{
    public function index(): JsonResponse
    {
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

        return response()->json($this->buildSummary($businessDate));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_date' => ['nullable', 'date_format:Y-m-d'],
            'declared_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $businessDate = $this->resolveBusinessDate($data['business_date'] ?? null);
        $summary = $this->buildSummary($businessDate);
        $declaredCash = round((float) $data['declared_cash'], 2);
        $expectedCash = round((float) $summary['totals']['cash_sales'], 2);

        $closure = CashClosure::query()->updateOrCreate(
            ['business_date' => $businessDate],
            [
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
                'closed_at' => now(),
            ],
        );

        return response()->json($closure->load('closer:id,name,email'));
    }

    private function resolveBusinessDate(?string $date): string
    {
        $tz = (string) (config('app.timezone') ?: 'America/Lima');

        return $date
            ? Carbon::createFromFormat('Y-m-d', $date, $tz)->toDateString()
            : now($tz)->toDateString();
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
}
