<?php


namespace App\Http\Middleware;

use App\Exceptions\ValidationException;
use App\Http\ApiRequestParameters;
use App\Http\Validators\ApiAuditRequestValidator;
use App\Interfaces\IPTraceInterface;
use Illuminate\Http\Request;
use Closure;

class ValidateApiAuditLogRequest
{

    /**
     * @var IPTraceInterface
     */
    private $ipTraceRepository;

    public function __construct(IPTraceInterface $ipTraceRepository)
    {
        $this->ipTraceRepository = $ipTraceRepository;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next)
    {
        $validator = (new ApiAuditRequestValidator())->validate([
            ApiRequestParameters::IP_TRACE_ID => $request->get(ApiRequestParameters::IP_TRACE_ID)
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $request->merge([
            'request_ip_trace' => $this->ipTraceRepository->find($request->get(ApiRequestParameters::IP_TRACE_ID))
        ]);

        return $next($request);
    }

}
