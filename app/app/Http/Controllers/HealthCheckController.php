<?php


namespace App\Http\Controllers;

use App\Http\ApiRequestParameters;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{

    const STATUS_OK = 'OK';
    const STATUS_ERROR = 'ERROR';

    /**
     * @param Request $request
     * @return string
     */
    public function check(Request $request)
    {
        $error = false;
        $context = [];

        try {
            DB::connection()->getPdo();
            $context['Database'] = self::STATUS_OK;
        } catch (Exception $exception) {
            $error = true;
            $context['Database'] = self::STATUS_ERROR;
        }

        if ('redis' === config('cache.default')) {
            try {
                Redis::ping();
                $context['Redis'] = self::STATUS_OK;
            } catch (Exception $exception) {
                $error = true;
                $context['Redis'] = self::STATUS_ERROR;
            }
        }

        $fileNameOfCurrentLogFile = sprintf('logs/laravel-%s.log', date('Y-m-d'));

        try {
            Log::info('Health Check Database Log');
            $context['Database Log'] = self::STATUS_OK;
        } catch (Exception $exception) {
            $error = true;
            $context['Database Log'] = self::STATUS_ERROR;
        }

        if (is_writable(storage_path()) && is_writable(storage_path('logs'))) {

            $context['Logs Directory Writeable'] = self::STATUS_OK;

            if (file_exists(storage_path($fileNameOfCurrentLogFile))) {
                if (is_writable(storage_path($fileNameOfCurrentLogFile))) {
                    $context['Daily Logs File Writeable'] = self::STATUS_OK;
                } else {
                    $error = true;
                    $context['Daily Logs File Writeable'] = self::STATUS_ERROR;
                }
            } else {
                $error = true;
                $context['Daily Logs File Writeable'] = self::STATUS_ERROR;
            }

        } else {
            $error = true;
            $context['Logs Directory Writeable'] = self::STATUS_ERROR;
        }

        if ($error) {

            $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
            $responseData = [
                'status' => self::STATUS_ERROR,
                'code' => $statusCode,
            ];

        } else {

            $statusCode =  Response::HTTP_OK;
            $responseData = [
                'status' => self::STATUS_OK,
                'code' => $statusCode
            ];

        }

        if ($request->get(ApiRequestParameters::HEALTH_DETAILED)) {
            $responseData['message'] = $context;
        }

        return response()->api(
            $responseData,
            $statusCode
        );

    }

}
