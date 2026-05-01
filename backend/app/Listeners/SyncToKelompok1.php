<?php

namespace App\Listeners;

use App\Events\DataApproved;
use App\Services\IntegrationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncToKelompok1 implements ShouldQueue
{
    use InteractsWithQueue;

    protected $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    public function handle(DataApproved $event): void
    {
        // Memanggil service integrasi saat event dieksekusi
        $this->integrationService->syncData($event->data, $event->type);
    }
}