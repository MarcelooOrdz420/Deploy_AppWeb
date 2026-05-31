<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/productos');
Route::redirect('/app', '/productos');
Route::view('/productos', 'store.products')->name('store.products');
Route::view('/quienes-somos', 'store.about')->name('store.about');
Route::view('/ubicacion', 'store.location')->name('store.location');
Route::view('/expertos', 'store.experts')->name('store.experts');
Route::view('/carrito', 'store.cart')->name('store.cart');
Route::view('/mis-pedidos', 'store.orders')->name('store.orders');
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');

Route::get('/admin/dashboard', [DashboardController::class, 'index']);
Route::redirect('/admin', '/admin/login');
Route::view('/admin/login', 'admin.login')->name('admin.login');
Route::view('/admin/panel', 'admin.panel')->name('admin.panel');

Route::get('/descargar-apk', function () {
    $path = public_path('downloads/AppMovilPollos.apk');
    abort_unless(is_file($path), 404, 'APK no encontrado en el servidor.');

    return response()->download($path, 'AppMovilPollos.apk');
})->name('apk.download');
