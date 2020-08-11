<?php


namespace App\Http\Middleware;

use App\Exceptions\UnauthorisedInternalAccessException;
use App\Models\Consumer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Validate3evConsumer
{

    const INTERNAL_COMPANY_EMAIL_IDENTIFIER = '@3ev.com';

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws UnauthorisedInternalAccessException
     */
    public function handle(Request $request, Closure $next)
    {

        /** @var Consumer $consumer */
        $consumer = auth()->user();

        if (!Str::contains($consumer->contact_email, self::INTERNAL_COMPANY_EMAIL_IDENTIFIER)) {
            throw new UnauthorisedInternalAccessException();
        }

        return $next($request);
    }

}
