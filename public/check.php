<?php
// Script de diagnóstico - ELIMINAR después de usar
$root = dirname(__DIR__);
$ok = '✅';
$fail = '❌';
$warn = '⚠️';

function check($label, $result, $detail = '') {
    $icon = $result ? '✅' : '❌';
    echo "{$icon} {$label}" . ($detail ? " — <span style='color:#aaa'>{$detail}</span>" : '') . "\n";
}

function perm($path) {
    if (!file_exists($path)) return 'NO EXISTE';
    return substr(sprintf('%o', fileperms($path)), -4);
}

echo '<!DOCTYPE html><html><head><meta charset="utf-8">
<title>Laravel Check</title>
<style>body{background:#0d1117;color:#c9d1d9;font-family:monospace;padding:30px;font-size:14px;}
h2{color:#58a6ff;border-bottom:1px solid #30363d;padding-bottom:8px;}
pre{background:#161b22;padding:15px;border-radius:6px;overflow:auto;line-height:1.8;}
.err{color:#f85149;} .ok{color:#3fb950;} .warn{color:#d29922;}
</style></head><body>';

echo '<h2>🔍 Diagnóstico Laravel — ' . date('Y-m-d H:i:s') . '</h2><pre>';

// 1. PHP
echo "<b>── PHP ──────────────────────────────</b>\n";
check('Versión PHP', true, PHP_VERSION);
check('PHP >= 8.2', version_compare(PHP_VERSION, '8.2.0', '>='), PHP_VERSION);

// 2. Archivos críticos
echo "\n<b>── Archivos críticos ────────────────</b>\n";
check('.env existe',         file_exists($root . '/.env'),                   $root . '/.env');
check('.htaccess existe',    file_exists(__DIR__ . '/.htaccess'),             __DIR__ . '/.htaccess');
check('vendor/autoload.php', file_exists($root . '/vendor/autoload.php'));
check('bootstrap/app.php',   file_exists($root . '/bootstrap/app.php'));
check('index.php',           file_exists(__DIR__ . '/index.php'));

// 3. .env contenido básico
echo "\n<b>── .env valores clave ───────────────</b>\n";
if (file_exists($root . '/.env')) {
    $env = file_get_contents($root . '/.env');
    check('APP_KEY definido',  preg_match('/^APP_KEY=base64:.+/m', $env));
    check('APP_ENV=production',preg_match('/^APP_ENV=production/m', $env));
    check('APP_DEBUG=false',   preg_match('/^APP_DEBUG=false/m', $env));
    preg_match('/^APP_URL=(.+)/m', $env, $m);
    check('APP_URL', true, trim($m[1] ?? 'no encontrado'));
    preg_match('/^DB_DATABASE=(.+)/m', $env, $m);
    check('DB_DATABASE', true, trim($m[1] ?? 'no encontrado'));
} else {
    echo "❌ No se puede leer .env\n";
}

// 4. Permisos de directorios
echo "\n<b>── Permisos (necesitan ser escribibles) ─</b>\n";
$dirs = [
    'storage'                     => $root . '/storage',
    'storage/logs'                => $root . '/storage/logs',
    'storage/framework'           => $root . '/storage/framework',
    'storage/framework/cache'     => $root . '/storage/framework/cache',
    'storage/framework/sessions'  => $root . '/storage/framework/sessions',
    'storage/framework/views'     => $root . '/storage/framework/views',
    'storage/app'                 => $root . '/storage/app',
    'bootstrap/cache'             => $root . '/bootstrap/cache',
];
foreach ($dirs as $label => $path) {
    $exists    = file_exists($path);
    $writable  = is_writable($path);
    $permStr   = perm($path);
    $status    = $exists && $writable;
    check($label . ' (' . $permStr . ')', $status, $writable ? 'escribible' : ($exists ? 'NO escribible ⚠' : 'NO EXISTE'));
}

// 5. Extensiones PHP requeridas
echo "\n<b>── Extensiones PHP ──────────────────</b>\n";
$exts = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl'];
foreach ($exts as $ext) {
    check($ext, extension_loaded($ext));
}

// 6. Intento de conectar MySQL
echo "\n<b>── Base de datos ────────────────────</b>\n";
if (file_exists($root . '/.env')) {
    $env = file_get_contents($root . '/.env');
    preg_match('/^DB_HOST=(.+)/m',     $env, $h);
    preg_match('/^DB_PORT=(.+)/m',     $env, $p);
    preg_match('/^DB_DATABASE=(.+)/m', $env, $db);
    preg_match('/^DB_USERNAME=(.+)/m', $env, $u);
    preg_match('/^DB_PASSWORD="?(.+?)"?$/m', $env, $pw);

    $host = trim($h[1] ?? '127.0.0.1');
    $port = trim($p[1] ?? '3306');
    $dbname = trim($db[1] ?? '');
    $user = trim($u[1] ?? '');
    $pass = trim($pw[1] ?? '');

    try {
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname}", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
        check('Conexión MySQL', true, "{$host}:{$port}/{$dbname}");
        $tables = $pdo->query("SHOW TABLES")->fetchAll();
        check('Tablas en BD', true, count($tables) . ' tablas encontradas');
    } catch (PDOException $e) {
        check('Conexión MySQL', false, htmlspecialchars($e->getMessage()));
    }
}

// 7. Intentar cargar Laravel
echo "\n<b>── Bootstrap Laravel ────────────────</b>\n";
try {
    require $root . '/vendor/autoload.php';
    $app = require_once $root . '/bootstrap/app.php';
    check('Laravel bootstrap', true, 'App cargada correctamente');
    check('Laravel versión', true, app()->version());
} catch (\Throwable $e) {
    check('Laravel bootstrap', false, htmlspecialchars($e->getMessage()));
    echo "\n<span style='color:#f85149'>ERROR DETALLADO:\n" . htmlspecialchars($e->getMessage()) . "\nArchivo: " . $e->getFile() . "\nLínea: " . $e->getLine() . "</span>\n";
}

echo '</pre>';
echo '<p style="color:#6e7681;font-size:12px;">⚠️ Elimina este archivo (<code>/public/check.php</code>) inmediatamente después de revisar.</p>';
echo '</body></html>';
