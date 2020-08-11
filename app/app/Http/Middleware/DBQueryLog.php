<?php

namespace App\Http\Middleware;

use App\Environments;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DBQueryLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( Environments::PRODUCTION !== strtolower(config('app.env'))
            && config('app.debug')
            && config('app.debug_queries')
        ) {
            DB::connection()->enableQueryLog();
        }

        return $next($request);
    }

    public function terminate ($request, $response)
    {

        if ( Environments::PRODUCTION !== strtolower(config('app.env'))
            && config('app.debug')
            && config('app.debug_queries')
        ) {
            $context = [];
            foreach (DB::connection()->getQueryLog() as $log) {
                $log = $this->injectBindingsIntoQuery($log);

                $context['time'] = sprintf('%sms', $log['time']);

                $context['query'] = $log['query'];

                Log::debug($request->getRequestUri(), $context);
            }
        }

    }

    private function injectBindingsIntoQuery ($log)
    {
        $query = str_replace('?', "'%s'", $log['query']);

        $bindings = collect($log['bindings'])->map(function ($element) {
            return str_replace('"', '', $element);
        })->toArray();

        $log['query'] = vsprintf($query, $bindings);

        return $log;
    }


}
