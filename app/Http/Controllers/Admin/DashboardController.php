<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AdminAnalyticsService;

class DashboardController extends Controller
{
    public function index(AdminAnalyticsService $analyticsService)
    {
        $todaySales = (float) Order::query()
            ->whereDate('created_at', now()->toDateString())
            ->whereNotIn('status', [Order::STATUS_CANCELLED])
            ->sum('total_amount');

        $monthSales = (float) Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereNotIn('status', [Order::STATUS_CANCELLED])
            ->sum('total_amount');

        $pendingOrders = Order::query()
            ->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PREPARING,
                Order::STATUS_ON_THE_WAY,
            ])->count();

        $latestOrders = Order::query()
            ->latest()
            ->limit(10)
            ->get(['tracking_code', 'customer_name', 'total_amount', 'status', 'payment_method', 'payment_status', 'scheduled_for', 'created_at']);

        $analytics = $analyticsService->buildOverview();

        return view('admin.dashboard', [
            'todaySales' => $todaySales,
            'monthSales' => $monthSales,
            'pendingOrders' => $pendingOrders,
            'latestOrders' => $latestOrders,
            'analytics' => $analytics,
        ]);
    }
}
