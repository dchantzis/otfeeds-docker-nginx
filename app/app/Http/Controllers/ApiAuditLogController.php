<?php


namespace App\Http\Controllers;


use App\Exceptions\ApiException;
use App\Http\Resources\ApiAuditLogCollection;
use App\Interfaces\ApiAuditLogInterface;
use Illuminate\Http\Request;

class ApiAuditLogController extends Controller
{

    /**
     * @var ApiAuditLogInterface
     */
    private $apiAuditLogRepository;

    /**
     * IpTraceController constructor.
     *
     * @param ApiAuditLogInterface $apiAuditLogRepository
     */
    public function __construct(ApiAuditLogInterface $apiAuditLogRepository)
    {
        $this->apiAuditLogRepository = $apiAuditLogRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws ApiException
     */
    public function find(Request $request)
    {

        try {

            $apiAuditLogEntries = $this->apiAuditLogRepository->findByIpTraceId($request->request_ip_trace->id);

            $responseData = new ApiAuditLogCollection($apiAuditLogEntries);

        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage(), $exception->getCode());
        }

        return response()->api(
            $responseData
        );
    }

}
