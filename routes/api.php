<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminNotificationController;
use App\Http\Controllers\Api\EInvoiceController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\PusherAuthController;
use App\Http\Controllers\Api\PeruLookupController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PublicSettingsController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileAddressController;
use App\Http\Controllers\Api\ProductController;
use App\Events\ChatbotReplySent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::get('/settings/public', PublicSettingsController::class);

        Route::get('/orders/track/{trackingCode}', [OrderController::class, 'track']);

        Route::post('/chatbot/message', [ChatbotController::class, 'message']);
        Route::post('/pusher/auth', PusherAuthController::class);

        Route::middleware('auth.api')->group(function (): void {
            Route::get('/auth/me', [AuthController::class, 'me']);
            Route::post('/lookups/dni', [PeruLookupController::class, 'lookupDni']);
            Route::get('/profile/addresses', [ProfileAddressController::class, 'index']);
            Route::post('/profile/addresses', [ProfileAddressController::class, 'store']);
            Route::delete('/profile/addresses/{address}', [ProfileAddressController::class, 'destroy']);

            Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/my', [OrderController::class, 'myOrders']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::get('/orders/{order}/payments/culqi-checkout', [PaymentController::class, 'culqiCheckout']);
        Route::post('/orders/{order}/payment-proof', [OrderController::class, 'uploadPaymentProof']);
        Route::get('/orders/{order}/receipt-view', [OrderController::class, 'receiptView']);
        Route::get('/orders/{order}/receipt', [OrderController::class, 'downloadReceipt']);

            Route::middleware('admin')->group(function (): void {
            Route::post('/admin/notifications/offers', [AdminNotificationController::class, 'sendOffer']);
            Route::get('/admin/products', [ProductController::class, 'adminIndex']);
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{product}', [ProductController::class, 'update']);
            Route::delete('/products/{product}', [ProductController::class, 'destroy']);

            Route::get('/admin/orders', [OrderController::class, 'index']);
            Route::get('/admin/orders/stats', [OrderController::class, 'stats']);
            Route::get('/admin/orders/export', [OrderController::class, 'export']);
            Route::get('/admin/orders/{order}/einvoice/preview', [EInvoiceController::class, 'preview']);
            Route::post('/admin/orders/{order}/einvoice/send', [EInvoiceController::class, 'send']);
            Route::patch('/admin/orders/{order}/status', [OrderController::class, 'updateStatus']);
            Route::patch('/admin/orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus']);
            Route::delete('/admin/orders/{order}', [OrderController::class, 'destroy']);

            Route::get('/admin/users', [AdminUserController::class, 'index']);
            Route::patch('/admin/users/{user}', [AdminUserController::class, 'update']);
            Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy']);

            // Endpoints de prueba para notificaciones/chatbot con Pusher
            Route::post('/admin/notifications/chatbot', function (\Illuminate\Http\Request $request) {
                $data = $request->validate([
                    'channel' => ['required', 'string'],
                    'event' => ['required', 'string'],
                    'message' => ['required', 'string'],
                ]);

                broadcast(new ChatbotReplySent(new Channel($data['channel']), $data['message'], null, $data['event']))->toOthers();

                return response()->json(['ok' => true, 'channel' => $data['channel'], 'event' => $data['event'], 'message' => $data['message']]);
            });

            // login history
            Route::get('/admin/login-histories', [\App\Http\Controllers\Api\AdminLoginHistoryController::class, 'index']);
        });
    });
});
