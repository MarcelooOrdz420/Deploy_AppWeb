<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Web\StorageWebController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/productos');
Route::redirect('/app', '/productos');
Route::view('/productos', 'store.products')->name('store.products');
Route::get('/storage', [StorageWebController::class, 'index'])->name('store.storage');
Route::post('/storage', [StorageWebController::class, 'store'])->middleware('throttle:10,1')->name('store.storage.upload');
Route::get('/storage/descargar', [StorageWebController::class, 'download'])->middleware('signed')->name('store.storage.download');
Route::redirect('/productos/storage', '/storage');
Route::view('/quienes-somos', 'store.about')->name('store.about');
Route::view('/ubicacion', 'store.location')->name('store.location');
Route::view('/expertos', 'store.experts')->name('store.experts');
Route::view('/carrito', 'store.cart')->name('store.cart');
Route::match(['get', 'head', 'post'], '/mis-pedidos', fn () => view('store.orders'))
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('store.orders');
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/reset-password', 'auth.reset-password')->name('password.reset');

Route::redirect('/admin/dashboard', '/admin/panel');
Route::redirect('/admin', '/admin/login');
Route::view('/admin/login', 'admin.login')->name('admin.login');
Route::view('/admin/panel', 'admin.panel')->name('admin.panel');

Route::get('/descargar-apk', function () {
    $path = public_path('downloads/AppMovilPollos.apk');
    abort_unless(is_file($path), 404, 'APK no encontrado en el servidor.');

    return response()->download($path, 'AppMovilPollos.apk');
})->name('apk.download');

Route::match(['get', 'head', 'post'], '/izipay-ipn', [PaymentController::class, 'izipayWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('izipay.ipn');

Route::match(['get', 'head', 'post'], '/izipay-ipn.php', [PaymentController::class, 'izipayWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('izipay.ipn.php');

Route::get('/pago/izipay/{order}', function (\App\Models\Order $order, \Illuminate\Http\Request $request) {
    abort_if((string) $order->payment_gateway !== 'izipay' && (string) $order->payment_method !== 'izipay', 404);
    abort_if(trim((string) $request->query('form_token')) === '', 404);

    return view('payments.izipay-checkout', [
        'order' => $order,
        'formToken' => (string) $request->query('form_token'),
        'publicKey' => config('services.izipay.public_key'),
        'jsUrl' => config('services.izipay.js_url'),
        'cssUrl' => config('services.izipay.css_url'),
    ]);
})->name('izipay.checkout');
