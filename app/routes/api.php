<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::pattern('id', '[0-9]+');
Route::pattern('ext', 'json|xml');

Route::group([
    'middleware' => [
        'ip-trace',
        'init-api-audit-log-request-entry'
    ],
], function () {

    Route::get('/', function () {
        return Config::get('app.api_version');
    });

    Route::get('/ping', [
        'uses' => 'PingController'
    ]);

    Route::get('healthz', [
        'uses' => 'HealthCheckController@check'
    ]);

    Route::group([
        'middleware' => [
            'db-query-log',
            'auth:api',
            'validate-3ev-consumer',
        ],
    ], function () {

        Route::group([
            'middleware' => [
                'validate-log-access-request',
            ],
        ], function () {

            Route::get('iptrace', 'IpTraceController@index');

            Route::get('systemlogs', 'SystemLogController@index');

        });

        Route::get('apiauditlog', 'ApiAuditLogController@find')->middleware('validate-api-audit-log-request');

    });

    Route::prefix('v1')->group(function () {

        Route::get('ping/{id}.{ext}', 'PingController@test');

        Route::group([
            'middleware' => [
                'db-query-log',
                'auth:api',
            ],
        ], function() {

            Route::get('dwellings.{ext}', 'DwellingsController@index');

            Route::get('dwellings/{id}.{ext}', 'DwellingsController@show');

            Route::get('dwellings/{id}/availability.{ext}', 'DwellingsController@availability');

        });

    });

});
