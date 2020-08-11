<?php


namespace App\Services;


use App\Interfaces\IPTraceInterface;
use App\Repositories\IPTraceRepository;
use Carbon\Laravel\ServiceProvider;

class IPTraceServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(
            IPTraceInterface::class,
            IPTraceRepository::class
        );
    }

}
