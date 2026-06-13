<?php

use App\Services\Marketing\CustomerRecoveryCampaignService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
