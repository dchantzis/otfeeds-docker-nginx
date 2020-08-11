<?php


namespace App\Http\Middleware;


use App\Interfaces\ApiAuditLogInterface;
use App\Models\ApiAuditLog;
use Illuminate\Http\Request;
use Closure;

class InitApiAuditLogRequestEntry
{

    public function handle(Request $request, Closure $next)
    {
        // Create Api Audit Log entry for the REQUEST
        $apiAuditLogRequestEntry = app(ApiAuditLogInterface::class)->initSlim($request, [], ApiAuditLog::REQUEST);

        //  Every entry is dependant on the Ip Trace Id.
        //  The corresponding RESPONSE entry is created in App\Http\ApiResponse@toResponse()

        return $next($request);
    }

}
