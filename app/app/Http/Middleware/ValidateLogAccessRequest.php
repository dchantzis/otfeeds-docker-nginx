<?php


namespace App\Http\Middleware;

use App\Exceptions\ValidationException;
use App\Http\ApiRequestParameters;
use App\Http\Validators\LogRequestValidator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Closure;

class ValidateLogAccessRequest
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next)
    {

        $validator = (new LogRequestValidator())->validate([
            ApiRequestParameters::IP_TRACE_ID => $request->get(ApiRequestParameters::IP_TRACE_ID),
            ApiRequestParameters::LIMIT => $request->get(ApiRequestParameters::LIMIT),
            ApiRequestParameters::START_DATE => $request->get(ApiRequestParameters::START_DATE),
            ApiRequestParameters::END_DATE => $request->get(ApiRequestParameters::END_DATE)
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ($request->get(ApiRequestParameters::END_DATE)) {
            $endDate = Carbon::parse($request->get(ApiRequestParameters::END_DATE));

            // Handle the case where the the time was not specified in the request and it defaults to 00:00:00 (start of day)
            if ($endDate->eq($endDate->copy()->startOfDay())) {
                $endDate->endOfDay();
            }
        }

        $request->merge([
            ApiRequestParameters::IP_TRACE_ID => $request->get(ApiRequestParameters::IP_TRACE_ID) ?? null,
            ApiRequestParameters::LIMIT => $request->get(ApiRequestParameters::LIMIT) ?? 20,
            ApiRequestParameters::START_DATE => $request->get(ApiRequestParameters::START_DATE) ? Carbon::parse($request->get(ApiRequestParameters::START_DATE)) : null,
            ApiRequestParameters::END_DATE => $endDate ?? null,
        ]);

        return $next($request);
    }

}
