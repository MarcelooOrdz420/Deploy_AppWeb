<?php

use App\Services\Marketing\CustomerRecoveryCampaignService;
use App\Services\Payments\IzipayService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('izipay:diagnose', function (IzipayService $izipay) {
    if (! app()->environment('local')) {
        $this->error('Este diagnostico solo esta disponible en el entorno local.');

        return 1;
    }

    $url = $izipay->ipnTargetUrl();
    $parts = parse_url($url) ?: [];
    $this->table(['Comprobacion', 'Valor'], [
        ['enabled', config('services.izipay.enabled') ? 'true' : 'false'],
        ['shop_id configured', filled(config('services.izipay.shop_id')) ? 'true' : 'false'],
        ['rest_api_key configured', filled(config('services.izipay.rest_api_key')) ? 'true' : 'false'],
        ['public_key configured', filled(config('services.izipay.public_key')) ? 'true' : 'false'],
        ['hmac_key configured', filled(config('services.izipay.hmac_key')) ? 'true' : 'false'],
        ['effective IPN URL', $url],
        ['scheme', (string) ($parts['scheme'] ?? '')],
        ['host', (string) ($parts['host'] ?? '')],
    ]);

    return 0;
})->purpose('Muestra de forma segura la configuracion efectiva de Izipay.');

Artisan::command('customers:send-inactivity-recovery {--days=5}', function () {
    $stats = app(CustomerRecoveryCampaignService::class)
        ->sendInactiveUserEmails((int) $this->option('days'));

    $this->info('Correos de reactivacion por inactividad procesados.');
    $this->table(['sent', 'skipped', 'failed'], [array_values($stats)]);
})->purpose('Envia correos a clientes que no iniciaron sesion recientemente.');

Artisan::command('customers:send-abandoned-cart-recovery {--hours=3}', function () {
    $stats = app(CustomerRecoveryCampaignService::class)
        ->sendAbandonedCartEmails((int) $this->option('hours'));

    $this->info('Correos de carrito abandonado procesados.');
    $this->table(['sent', 'skipped', 'failed'], [array_values($stats)]);
})->purpose('Envia correos a clientes con carrito abandonado.');

Schedule::command('customers:send-inactivity-recovery --days=5')->dailyAt('10:00');
Schedule::command('customers:send-abandoned-cart-recovery --hours=3')->everyTwoHours();
