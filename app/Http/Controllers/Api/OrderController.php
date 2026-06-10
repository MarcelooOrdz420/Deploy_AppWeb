<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderCreatedAlertSent;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Services\Fcm\FcmClient;
use App\Services\InventoryMovementService;
use App\Services\SimplePdfReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = $this->buildAdminOrdersQuery(request())
            ->with(['items', 'user'])
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function export(Request $request): Response
    {
        $orders = $this->buildAdminOrdersQuery($request)
            ->with(['items'])
            ->latest()
            ->get();

        $header = [
            'Codigo',
            'Fecha registro',
            'Cliente',
            'Telefono',
            'Correo',
            'Entrega',
            'Direccion',
            'Referencia',
            'Reserva programada',
            'Ventana entrega',
            'Estado pedido',
            'Metodo de pago',
            'Estado de pago',
            'Operacion / Referencia',
            'Total',
            'Items',
        ];

        $rows = [];

        foreach ($orders as $order) {
            $items = $order->items
                ->map(function (OrderItem $item): string {
                    return "{$item->product_name} x{$item->quantity}";
                })
                ->implode(' | ');

            $rows[] = [
                $order->tracking_code,
                optional($order->created_at)?->format('Y-m-d H:i:s'),
                $order->customer_name,
                $order->customer_phone,
                $order->customer_email ?: '',
                $this->deliveryTypeLabel((string) $order->delivery_type),
                $order->address ?: '',
                $order->reference ?: '',
                optional($order->scheduled_for)?->format('Y-m-d H:i:s') ?: 'No',
                $order->delivery_window_label ?: '',
                $this->orderStatusLabel((string) $order->status),
                $this->paymentMethodLabel((string) $order->payment_method),
                $this->paymentStatusLabel((string) $order->payment_status),
                $order->payment_reference ?: '',
                number_format((float) $order->total_amount, 2, '.', ''),
                $items,
            ];
        }

        $content = $this->buildOrdersExcelHtml(
            request: $request,
            header: $header,
            rows: $rows,
            ordersCount: $orders->count(),
        );

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte-pedidos-admin.xls"',
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $orders = $this->buildAdminOrdersQuery($request)
            ->get([
                'id',
                'created_at',
                'total_amount',
                'status',
                'payment_method',
                'payment_status',
                'delivery_type',
            ]);

        $totalSales = (float) $orders->sum(fn (Order $order): float => (float) $order->total_amount);
        $ordersCount = $orders->count();
        $averageTicket = $ordersCount > 0 ? round($totalSales / $ordersCount, 2) : 0.0;

        $dayBuckets = $this->groupOrdersByPeriod($orders, 'day');
        $monthBuckets = $this->groupOrdersByPeriod($orders, 'month');
        $yearBuckets = $this->groupOrdersByPeriod($orders, 'year');

        $bestDay = collect($dayBuckets)->sortByDesc('total')->first();
        $worstDay = collect($dayBuckets)->sortBy('total')->first();

        $paymentBreakdown = $orders
            ->groupBy(fn (Order $order): string => (string) ($order->payment_method ?: 'n/a'))
            ->map(fn (Collection $group, string $method): array => [
                'method' => $method,
                'count' => $group->count(),
                'total' => round((float) $group->sum(fn (Order $order): float => (float) $order->total_amount), 2),
                'verified_count' => $group->where('payment_status', 'verified')->count(),
                'reported_count' => $group->where('payment_status', 'reported')->count(),
                'pending_count' => $group->where('payment_status', 'pending')->count(),
                'rejected_count' => $group->where('payment_status', 'rejected')->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $statusBreakdown = $orders
            ->groupBy(fn (Order $order): string => (string) ($order->status ?: 'n/a'))
            ->map(fn (Collection $group, string $status): array => [
                'status' => $status,
                'count' => $group->count(),
                'total' => round((float) $group->sum(fn (Order $order): float => (float) $order->total_amount), 2),
            ])
            ->sortByDesc('count')
            ->values();

        return response()->json([
            'summary' => [
                'orders_count' => $ordersCount,
                'total_sales' => round($totalSales, 2),
                'average_ticket' => $averageTicket,
                'best_day' => $bestDay,
                'worst_day' => $worstDay,
            ],
            'buckets' => [
                'day' => $dayBuckets,
                'month' => $monthBuckets,
                'year' => $yearBuckets,
            ],
            'payments' => $paymentBreakdown,
            'statuses' => $statusBreakdown,
        ]);
    }

    public function myOrders(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['items', 'statusHistory'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($request->user()->role !== 'admin' && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $order->load(['items', 'statusHistory']);

        return response()->json($order);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:120'],
            'delivery_type' => ['required', Rule::in(['pickup', 'delivery'])],
            'scheduled_for' => ['nullable', 'date'],
            'delivery_window_label' => ['nullable', 'string', 'max:120'],
            'payment_method' => ['required', Rule::in(['yape', 'plin', 'transfer', 'cod', 'culqi'])],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'billing_document_type' => ['nullable', Rule::in(['dni'])],
            'billing_document_number' => ['nullable', 'string', 'max:20'],
            'billing_name' => ['nullable', 'string', 'max:180'],
            'billing_email' => ['nullable', 'email', 'max:120'],
            'billing_address' => ['nullable', 'string', 'max:255'],
            'billing_receipt_type' => ['nullable', Rule::in(['boleta'])],
            'salad_type' => ['nullable', Rule::in(['dulce', 'salada'])],
            'drink_note' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'billing_metadata' => ['nullable', 'array'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($data['delivery_type'] === 'delivery' && empty($data['address'])) {
            return response()->json(['message' => 'Direccion requerida para delivery.'], 422);
        }

        $tz = (string) (config('app.timezone') ?: 'America/Lima');
        $now = now($tz);

        $scheduledFor = null;
        if (! empty($data['scheduled_for'])) {
            $scheduledFor = Carbon::parse($data['scheduled_for'], $tz);
            $minimumSchedule = $now->copy()->addMinutes(30);

            if ($scheduledFor->lt($minimumSchedule)) {
                return response()->json(['message' => 'La programacion debe ser de al menos 30 minutos hacia adelante.'], 422);
            }

            if ((int) $scheduledFor->format('H') > 23 || $scheduledFor->format('H:i') > '23:00') {
                return response()->json(['message' => 'La cocina cierra a las 11:00 PM. No se aceptan pedidos despues de esa hora.'], 422);
            }
        } elseif ($now->format('H:i') > '23:00') {
            return response()->json(['message' => 'La cocina ya cerro por hoy. Puedes programar un pedido antes de las 11:00 PM.'], 422);
        }

        if (! in_array($data['payment_method'], ['cod', 'culqi'], true) && empty($data['payment_reference'])) {
            return response()->json(['message' => 'Ingresa codigo/operacion del pago para validar.'], 422);
        }

        if (($data['billing_receipt_type'] ?? null) === 'boleta') {
            if (($data['billing_document_type'] ?? null) !== 'dni' || strlen((string) ($data['billing_document_number'] ?? '')) !== 8) {
                return response()->json(['message' => 'La boleta requiere DNI valido de 8 digitos.'], 422);
            }
            if (empty($data['billing_name'])) {
                return response()->json(['message' => 'La boleta requiere el nombre del cliente validado por DNI.'], 422);
            }
        }

        $productIds = collect($data['items'])->pluck('product_id')->all();
        $hasChickenProduct = Product::query()
            ->whereIn('id', $productIds)
            ->where('category', 'pollos')
            ->exists();

        if ($hasChickenProduct && empty($data['salad_type'])) {
            return response()->json(['message' => 'Selecciona ensalada dulce o salada para pedidos de pollo.'], 422);
        }

        $productsForLimitValidation = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'name', 'category']);

        if ($limitError = $this->validatePurchaseLimits(collect($data['items']), $productsForLimitValidation)) {
            return response()->json(['message' => $limitError], 422);
        }

        $ordersHasScheduledFor = Schema::hasColumn('orders', 'scheduled_for');
        $ordersHasDeliveryWindowLabel = Schema::hasColumn('orders', 'delivery_window_label');

        $order = DB::transaction(function () use ($data, $request, $scheduledFor, $ordersHasScheduledFor, $ordersHasDeliveryWindowLabel): Order {
            $trackingCode = strtoupper('ED-'.substr(uniqid(), -8));

            $orderData = [
                'user_id' => $request->user()->id,
                'tracking_code' => $trackingCode,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'delivery_type' => $data['delivery_type'],
                'status' => Order::STATUS_PENDING,
                'total_amount' => 0,
                'payment_method' => $data['payment_method'],
                'payment_gateway' => $data['payment_method'] === 'culqi' ? 'culqi' : null,
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_proof_path' => null,
                'payment_status' => in_array($data['payment_method'], ['cod', 'culqi'], true) ? 'pending' : 'pending',
                'payment_reported_at' => null,
                'payment_verified_at' => null,
                'billing_document_type' => $data['billing_document_type'] ?? null,
                'billing_document_number' => $data['billing_document_number'] ?? null,
                'billing_name' => $data['billing_name'] ?? null,
                'billing_email' => $data['billing_email'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'billing_receipt_type' => $data['billing_receipt_type'] ?? null,
                'billing_metadata' => $data['billing_metadata'] ?? null,
                'salad_type' => $data['salad_type'] ?? null,
                'drink_note' => $data['drink_note'] ?? null,
                'address' => $data['address'] ?? null,
                'reference' => $data['reference'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];

            if ($ordersHasScheduledFor) {
                $orderData['scheduled_for'] = $scheduledFor;
            }

            if ($ordersHasDeliveryWindowLabel) {
                $orderData['delivery_window_label'] = $data['delivery_window_label'] ?? null;
            }

            $order = Order::create($orderData);

            $total = 0;

            foreach ($data['items'] as $item) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($item['product_id']);

                if (! $product->is_available) {
                    abort(response()->json([
                        'message' => "El producto {$product->name} no esta activo actualmente.",
                    ], 422));
                }

                if ((int) $product->stock <= 0) {
                    abort(response()->json([
                        'message' => "El platillo {$product->name} esta agotado por el momento.",
                    ], 422));
                }

                if ((int) $product->stock < (int) $item['quantity']) {
                    abort(response()->json([
                        'message' => "Solo quedan {$product->stock} unidades disponibles de {$product->name}.",
                    ], 422));
                }

                $lineTotal = $product->price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                ]);

                $product->decrement('stock', (int) $item['quantity']);
                $product->refresh();

                app(InventoryMovementService::class)->logSale(
                    order: $order,
                    product: $product,
                    quantity: (int) $item['quantity'],
                    actor: $request->user(),
                );

                $total += $lineTotal;
            }

            $order->update(['total_amount' => $total]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => Order::STATUS_PENDING,
                'note' => 'Pedido creado',
                'changed_by' => $request->user()->id,
            ]);

            return $order->load(['items', 'statusHistory']);
        });

        try {
            event(new OrderCreatedAlertSent($order));
        } catch (\Throwable) {
            // Silencioso: el pedido no debe fallar si Pusher no esta configurado o falla.
        }

        return response()->json($order, 201);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PREPARING,
                Order::STATUS_ON_THE_WAY,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
            ])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $order = DB::transaction(function () use ($data, $order, $request): Order {
            $previousStatus = (string) $order->status;

            $order->update([
                'status' => $data['status'],
            ]);

            $this->syncInventoryForStatusTransition(
                order: $order->fresh(['items']),
                previousStatus: $previousStatus,
                nextStatus: $data['status'],
                actor: $request->user(),
            );

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
                'changed_by' => $request->user()->id,
            ]);

            return $order->fresh(['items', 'statusHistory']);
        });

        $this->sendOrderStatusPush($order);

        return response()->json($order);
    }

    public function updatePaymentStatus(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'payment_status' => ['required', Rule::in(['pending', 'reported', 'verified', 'rejected'])],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->isDigitalPaymentMethod((string) $order->payment_method)) {
            if (! $order->payment_proof_path && in_array($data['payment_status'], ['reported', 'verified'], true)) {
                return response()->json([
                    'message' => 'No puedes marcar este pago como reportado o verificado sin comprobante subido.',
                ], 422);
            }
        }

        $order->update([
            'payment_status' => $data['payment_status'],
            'payment_reference' => $data['payment_reference'] ?? $order->payment_reference,
            'payment_verified_at' => $data['payment_status'] === 'verified' ? now() : null,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'note' => $data['note'] ?? ('Pago actualizado: '.$data['payment_status']),
            'changed_by' => $request->user()->id,
        ]);

        $this->sendOrderStatusPush($order, paymentStatus: $data['payment_status']);

        return response()->json($order->fresh(['items', 'statusHistory']));
    }

    private function sendOrderStatusPush(Order $order, ?string $paymentStatus = null): void
    {
        try {
            $userId = (int) $order->user_id;
            if ($userId <= 0) return;

            /** @var FcmClient $client */
            $client = app(FcmClient::class);
            if (! $client->isConfigured()) return;

            $status = (string) ($order->status ?? '');
            $tracking = (string) ($order->tracking_code ?? '');
            $title = 'Actualizacion de pedido';
            $body = $tracking !== '' ? "Pedido {$tracking}: {$status}" : "Pedido actualizado: {$status}";
            if ($paymentStatus) {
                $body .= " | Pago: {$paymentStatus}";
            }

            $client->sendToTopic(
                topic: "orders_user_{$userId}",
                notification: [
                    'title' => $title,
                    'body' => $body,
                ],
                data: [
                    'route' => '/orders',
                    'tracking_code' => $tracking,
                    'status' => $status,
                    'payment_status' => $paymentStatus ?? (string) ($order->payment_status ?? ''),
                ],
            );
        } catch (\Throwable) {
            // Silencioso: no romper el flujo de admin si FCM no esta configurado o falla.
        }
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $deletedTrackingCode = $order->tracking_code;

        $order->delete();

        return response()->json([
            'message' => 'Pedido eliminado',
            'deleted_tracking_code' => $deletedTrackingCode,
        ]);
    }

    public function uploadPaymentProof(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
        ]);

        if (! $this->isDigitalPaymentMethod((string) $order->payment_method)) {
            return response()->json([
                'message' => 'Solo Yape, Plin y Transferencia requieren comprobante digital.',
            ], 422);
        }

        $path = $request->file('proof')->store('payment-proofs', 'public');
        $publicPath = '/storage/'.$path;

        $order->update([
            'payment_proof_path' => $publicPath,
            'payment_reference' => $data['payment_reference'] ?? $order->payment_reference,
            'payment_status' => 'reported',
            'payment_reported_at' => now(),
        ]);

        return response()->json([
            'message' => 'Comprobante subido correctamente',
            'order' => $order->fresh(['items', 'statusHistory']),
        ]);
    }

    public function downloadReceipt(Request $request, Order $order, SimplePdfReceiptService $pdfReceiptService): Response
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            return response('No autorizado', 403);
        }

        $content = $pdfReceiptService->generate($order);
        $filename = 'boleta-'.$order->tracking_code.'.pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function receiptView(Request $request, Order $order, SimplePdfReceiptService $pdfReceiptService): Response
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            return response('No autorizado', 403);
        }

        return response($pdfReceiptService->generate($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="boleta-'.$order->tracking_code.'.pdf"',
        ]);
    }

    public function track(string $trackingCode): JsonResponse
    {
        $order = Order::query()
            ->with(['items', 'statusHistory'])
            ->where('tracking_code', strtoupper($trackingCode))
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        return response()->json([
            'tracking_code' => $order->tracking_code,
            'status' => $order->status,
            'delivery_type' => $order->delivery_type,
            'scheduled_for' => optional($order->scheduled_for)?->toDateTimeString(),
            'delivery_window_label' => $order->delivery_window_label,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'salad_type' => $order->salad_type,
            'drink_note' => $order->drink_note,
            'items' => $order->items,
            'status_history' => $order->statusHistory->map(fn (OrderStatusHistory $history): array => [
                'status' => $history->status,
                'note' => $history->note,
                'created_at' => optional($history->created_at)?->toDateTimeString(),
            ])->values(),
        ]);
    }

    private function buildAdminOrdersQuery(Request $request)
    {
        $query = Order::query();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status')->toString());
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method')->toString());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from')->toString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to')->toString());
        }

        return $query;
    }

    private function buildOrdersExcelHtml(Request $request, array $header, array $rows, int $ordersCount): string
    {
        $title = 'REPORTE DE PEDIDOS ADMINISTRATIVOS';
        $generatedAt = now()->format('Y-m-d H:i:s');
        $filters = array_filter([
            $request->filled('status') ? 'Estado: '.$this->orderStatusLabel($request->string('status')->toString()) : null,
            $request->filled('payment_status') ? 'Pago: '.$this->paymentStatusLabel($request->string('payment_status')->toString()) : null,
            $request->filled('payment_method') ? 'Metodo: '.$this->paymentMethodLabel($request->string('payment_method')->toString()) : null,
            $request->filled('date_from') ? 'Desde: '.$request->string('date_from')->toString() : null,
            $request->filled('date_to') ? 'Hasta: '.$request->string('date_to')->toString() : null,
        ]);

        $filtersLine = $filters ? implode(' | ', $filters) : 'Sin filtros aplicados';
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
            $rowsHtml = '<tr><td colspan="'.$colspan.'" class="empty-row">No hay pedidos para exportar con los filtros actuales.</td></tr>';
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
        .report-filters { background: #fff9f2; color: #5a4639; }
        thead th { background: #f28c18; color: #111111; font-weight: 700; text-align: left; }
        tbody tr:nth-child(even) td { background: #fff7ef; }
        tbody tr:nth-child(odd) td { background: #ffffff; }
        .empty-row { text-align: center; font-weight: 700; color: #7b5c45; }
    </style>
</head>
<body>
    <table>
        <tr><td colspan="{$colspan}" class="report-title">{$this->excelCell($title)}</td></tr>
        <tr><td colspan="{$colspan}" class="report-meta">Generado: {$this->excelCell($generatedAt)} | Registros: {$ordersCount}</td></tr>
        <tr><td colspan="{$colspan}" class="report-filters">{$this->excelCell($filtersLine)}</td></tr>
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

    private function deliveryTypeLabel(string $deliveryType): string
    {
        return match ($deliveryType) {
            'pickup' => 'Recojo en tienda',
            'delivery' => 'Delivery',
            default => $deliveryType,
        };
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            Order::STATUS_PENDING => 'Pendiente',
            Order::STATUS_CONFIRMED => 'Confirmado',
            Order::STATUS_PREPARING => 'En preparacion',
            Order::STATUS_ON_THE_WAY => 'En camino',
            Order::STATUS_DELIVERED => 'Entregado',
            Order::STATUS_CANCELLED => 'Cancelado',
            default => $status,
        };
    }

    private function paymentMethodLabel(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'yape' => 'Yape',
            'plin' => 'Plin',
            'transfer' => 'Transferencia',
            'cod' => 'Contraentrega',
            'culqi' => 'Culqi',
            default => $paymentMethod,
        };
    }

    private function paymentStatusLabel(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'pending' => 'Pendiente',
            'reported' => 'Reportado',
            'verified' => 'Verificado',
            'rejected' => 'Rechazado',
            default => $paymentStatus,
        };
    }

    private function isDigitalPaymentMethod(string $paymentMethod): bool
    {
        return in_array($paymentMethod, ['yape', 'plin', 'transfer'], true);
    }

    private function groupOrdersByPeriod(Collection $orders, string $period): array
    {
        return $orders
            ->filter(fn (Order $order): bool => $order->created_at !== null)
            ->groupBy(function (Order $order) use ($period): string {
                return match ($period) {
                    'day' => $order->created_at->format('Y-m-d'),
                    'month' => $order->created_at->format('Y-m'),
                    'year' => $order->created_at->format('Y'),
                    default => $order->created_at->format('Y-m-d'),
                };
            })
            ->map(function (Collection $group, string $key): array {
                return [
                    'key' => $key,
                    'label' => $key,
                    'count' => $group->count(),
                    'total' => round((float) $group->sum(fn (Order $order): float => (float) $order->total_amount), 2),
                ];
            })
            ->sortBy('key')
            ->values()
            ->all();
    }

    private function validatePurchaseLimits(Collection $items, Collection $products): ?string
    {
        $productsById = $products->keyBy('id');
        $namedTotals = [];
        $gaseosaTotal = 0;

        foreach ($items as $item) {
            $product = $productsById->get((int) $item['product_id']);
            if (! $product) {
                continue;
            }

            $quantity = (int) $item['quantity'];
            $normalizedName = $this->normalizeProductName($product->name);
            $namedTotals[$normalizedName] = ($namedTotals[$normalizedName] ?? 0) + $quantity;

            if ($this->isLimitedSoda($product)) {
                $gaseosaTotal += $quantity;
            }
        }

        $exactNameLimits = [
            'pollo entero a la brasa' => 1,
            'mega combo familiar' => 1,
            '1/2 pollo a la brasa' => 2,
            '1/4 pollo a la brasa' => 4,
            'mostrito tradicional' => 4,
            'chicha morada 1l' => 2,
            'limonada frozen' => 2,
        ];

        foreach ($exactNameLimits as $normalizedName => $maxUnits) {
            $currentUnits = $namedTotals[$normalizedName] ?? 0;

            if ($currentUnits > $maxUnits) {
                $productLabel = $products
                    ->first(fn (Product $product): bool => $this->normalizeProductName($product->name) === $normalizedName)
                    ?->name ?? $normalizedName;

                return "Solo se permiten {$maxUnits} unidades de {$productLabel} por cliente en cada pedido.";
            }
        }

        if ($gaseosaTotal > 3) {
            return 'Solo se permiten 3 gaseosas personales por cliente en cada pedido.';
        }

        return null;
    }

    private function isLimitedSoda(Product $product): bool
    {
        if ($product->category !== 'bebidas') {
            return false;
        }

        return in_array($this->normalizeProductName($product->name), [
            'coca-cola personal 500ml',
            'inca kola personal 500ml',
            'sprite personal 500ml',
        ], true);
    }

    private function normalizeProductName(string $name): string
    {
        $normalized = strtolower(trim($name));
        $asciiMap = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ñ' => 'n',
        ];

        return strtr($normalized, $asciiMap);
    }

    private function syncInventoryForStatusTransition(Order $order, string $previousStatus, string $nextStatus, $actor): void
    {
        if ($previousStatus === $nextStatus) {
            return;
        }

        $movementService = app(InventoryMovementService::class);

        if ($previousStatus !== Order::STATUS_CANCELLED && $nextStatus === Order::STATUS_CANCELLED) {
            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = Product::query()
                    ->lockForUpdate()
                    ->find($item->product_id);

                if (! $product) {
                    continue;
                }

                $stockBefore = (int) $product->stock;
                $product->increment('stock', (int) $item->quantity);
                $product->refresh();

                $movementService->logCancellationReturn(
                    order: $order,
                    product: $product,
                    quantity: (int) $item->quantity,
                    stockBefore: $stockBefore,
                    stockAfter: (int) $product->stock,
                    actor: $actor,
                );
            }

            return;
        }

        if ($previousStatus === Order::STATUS_CANCELLED && $nextStatus !== Order::STATUS_CANCELLED) {
            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = Product::query()
                    ->lockForUpdate()
                    ->find($item->product_id);

                if (! $product) {
                    continue;
                }

                if ((int) $product->stock < (int) $item->quantity) {
                    abort(response()->json([
                        'message' => "No hay stock suficiente para reactivar el pedido {$order->tracking_code}.",
                    ], 422));
                }

                $product->decrement('stock', (int) $item->quantity);
                $product->refresh();

                $movementService->logSale(
                    order: $order,
                    product: $product,
                    quantity: (int) $item->quantity,
                    actor: $actor,
                );
            }
        }
    }

}
