<?php


namespace App\Providers;

use App\Http\ApiResponse;
use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Response as SymfonyResponse;
use Exception;

class ResponseApiResponseMacroServiceProvider extends ServiceProvider
{

    public function boot()
    {
        Response::macro('api', function ($content, $httpStatus = SymfonyResponse::HTTP_OK, Exception $exception = null, $headers = [], $options = 0) {

            return (new ApiResponse(
                $content,
                $httpStatus,
                $exception,
                $headers,
                $options))->toResponse();

        });
    }

}
