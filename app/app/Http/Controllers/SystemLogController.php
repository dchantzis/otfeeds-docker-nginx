<?php


namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Resources\SystemLogCollection;
use App\Interfaces\SystemLogInterface;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{

    /**
     * @var SystemLogInterface
     */
    private $systemLogRepository;

    /**
     * SystemLogController constructor.
     *
     * @param SystemLogInterface $systemLogRepository
     */
    public function __construct(SystemLogInterface $systemLogRepository)
    {
        $this->systemLogRepository = $systemLogRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws ApiException
     */
    public function index(Request $request)
    {
        try {
            $systemLogEntries = $this->systemLogRepository->get($request->ip_trace_id, $request->limit, 0, $request->start_date, $request->end_date);

            $responseData = new SystemLogCollection($systemLogEntries);
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage(), $exception->getCode());
        }

        return response()->api(
            $responseData
        );
    }

}
