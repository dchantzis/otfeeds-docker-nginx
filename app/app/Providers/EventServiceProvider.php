<?php

namespace App\Providers;

use App\Events\LogMonologEvent;
use App\Listeners\LogMonologEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
//        /* Default with installation. Not used */
//        Registered::class => [
//            SendEmailVerificationNotification::class,
//        ],

        LogMonologEvent::class => [
            //  The LogMonologEventListener acts as the subscriber as well.
            //  It's intentionally defined here.
            LogMonologEventListener::class
        ]
    ];

    protected $subscribe = [
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
