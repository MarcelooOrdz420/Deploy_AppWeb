<?php
/**
 * Script temporal para crear o actualizar el usuario administrador desde hosting/cPanel.
 * IMPORTANTE: eliminar este archivo despues de usarlo.
 */

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'admin@eldorado.pe';
$password = 'admin12345';

$user = \App\Models\User::query()->updateOrCreate(
    ['email' => $email],
    [
        'name' => 'ADMINISTRADOR',
        'phone' => null,
        'role' => 'admin',
        'is_active' => true,
        'password' => \Illuminate\Support\Facades\Hash::make($password),
    ]
);

header('Content-Type: text/plain; charset=UTF-8');
echo "ADMIN OK\n";
echo "ID: {$user->id}\n";
echo "EMAIL: {$user->email}\n";
echo "ROLE: {$user->role}\n";
echo "ACTIVE: ".($user->is_active ? '1' : '0')."\n";
echo "\nElimina public/create-admin.php despues de usarlo.\n";
