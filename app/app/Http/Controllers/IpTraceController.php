<?php


namespace App\Http\Controllers;


use App\Exceptions\ApiException;
use App\Http\Resources\IpTraceCollection;
use App\Interfaces\IPTraceInterface;
use Illuminate\Http\Request;

class IpTraceController extends Controller
{

    /**
     * @var IPTraceInterface
     */
    private $ipTraceRepository;

    /**
     * IpTraceController constructor.
     *
     * @param IPTraceInterface $ipTraceRepository
     */
    public function __construct(IPTraceInterface $ipTraceRepository)
    {
        $this->ipTraceRepository = $ipTraceRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws ApiException
     */
    public function index(Request $request)
    {

        try {
            $ipTraceEntries = $this->ipTraceRepository->get($request->ip_trace_id, $request->limit, 0, $request->start_date, $request->end_date);

            $responseData = new IpTraceCollection($ipTraceEntries);
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage(), $exception->getCode());
        }

        return response()->api(
            $responseData
        );

    }

}
