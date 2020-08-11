<?php


namespace App\Providers;

use App\Auth\ApiConsumerProvider;
use App\Models\Consumer;
use Carbon\Laravel\ServiceProvider;
use Illuminate\Auth;

class ApiAuthProvider extends ServiceProvider
{

    public function register()
    {
        //
    }

    public function boot()
    {
//        Auth::provider('eloquent', function ()
//        {
//           return new ApiConsumerProvider(new Consumer());
//        });
    }

}
