<?php


namespace App\Http\Middleware;


use App\Interfaces\IPTraceInterface;
use Illuminate\Http\Request;
use Closure;

class IPTraceMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $consumerId = null;
        if ($consumer = auth()->user()) {
            $consumerId = $consumer->id;
        }

        $ipTrace = app(IPTraceInterface::class)->generate(
            $request->ip(),
            $consumerId,
            $request->method(),
            $request->getUri(),
            $request->all(),
            $request->header(),
            $request->getHost()
        )->getModel();

        $request->merge([
            'ip_trace' => $ipTrace
        ]);

        return $next($request);
    }

}
