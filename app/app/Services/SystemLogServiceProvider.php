<?php


namespace App\Services;

use App\Interfaces\SystemLogInterface;
use App\Repositories\SystemLogRepository;
use Carbon\Laravel\ServiceProvider;


class SystemLogServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(
          SystemLogInterface::class,
          SystemLogRepository::class
        );
    }

}
