<?php


namespace App\Services;


use App\Interfaces\ApiAuditLogInterface;
use App\Repositories\ApiAuditLogRepository;
use Carbon\Laravel\ServiceProvider;

class ApiAuditLogServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(
            ApiAuditLogInterface::class,
            ApiAuditLogRepository::class
        );
    }

}
