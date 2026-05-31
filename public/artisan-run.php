<?php
/**
 * Script temporal para limpiar caché de Laravel desde cPanel.
 * IMPORTANTE: Eliminar este archivo después de usarlo.
 */

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$commands = [
    'config:clear',
    'cache:clear',
    'view:clear',
    'route:clear',
];

echo '<pre style="font-family:monospace;background:#1a1a2e;color:#00ff88;padding:20px;font-size:14px;">';
echo "=== Laravel Cache Cleaner ===\n\n";

foreach ($commands as $cmd) {
    $code = $kernel->call($cmd);
    $status = $code === 0 ? '✅ OK' : '❌ ERROR (code ' . $code . ')';
    echo "php artisan {$cmd} ... {$status}\n";
}

echo "\n✅ Listo. Elimina este archivo ahora desde el Administrador de Archivos de cPanel.\n";
echo '</pre>';
