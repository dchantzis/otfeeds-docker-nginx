<?php


namespace App\Repositories;


use App\Interfaces\IPTraceInterface;
use App\Models\IPTrace;

class IPTraceRepository extends AbstractRepository implements IPTraceInterface
{

    private $ignoredFields = [
        // Add attributes to hide in the request_method column
    ];

    /**
     * IPTraceRepository constructor.
     * @param IPTrace $model
     */
    public function __construct(IPTrace $model)
    {
        $this->model = $model;

        parent::__construct($model);
    }

    /**
     * @return string
     */
    public function model()
    {
        return IPTrace::class;
    }

    /**
     * @param $ipAddress
     * @param $consumerId
     * @param $requestMethod
     * @param $route
     * @param $requestParameters
     * @param $requestHeader
     * @param $host
     *
     * @return mixed
     */
    public function generate($ipAddress, $consumerId, $requestMethod, $route, $requestParameters, $requestHeader, $host)
    {

        if (!empty($requestMethod)) {

            $intersect = array_intersect(array_keys($requestParameters), $this->ignoredFields);

            if (count($intersect)) {
                $ignoredFields = array_fill_keys($intersect, '********');

                $requestParameters = array_replace($requestParameters, $ignoredFields);
            }

            $requestParameters = json_encode($requestParameters, JSON_PRETTY_PRINT);
        } else {
            $requestParameters = '';
        }

        $requestParametersString = (is_array($requestParameters)) ? json_encode($requestParameters) : $requestParameters;
        $requestHeaderString = (is_array($requestHeader)) ? json_encode($requestHeader) : $requestHeader;

        return $this->getModel()->create([
            'ip_address' => $ipAddress,
            'consumer_id' => $consumerId,
            'request_method' => $requestMethod,
            'route' => $route,
            'request_parameters' => $requestParametersString,
            'request_header' => $requestHeaderString,
            'host' => $host,
        ]);
    }

    /**
     * @param int $ipTraceId
     * @param int $limit
     * @param int $offset
     * @param null $startDate
     * @param null $endDate
     *
     * @return mixed
     */
    public function get($ipTraceId = null, $limit = 10, $offset = 0, $startDate = null, $endDate = null)
    {
        $query = $this->getModel()
            ->orderBy('created_at', 'desc');

        if (!is_null($ipTraceId)) {
            $query->where('id', $ipTraceId);
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query->limit($limit)
                ->offset($offset);
        }

        // No date range is specified
        if (is_null($startDate) && is_null($endDate)) {
            return $query->get();
        }

        // Only start date is specified
        if (!is_null($startDate) && is_null($endDate)) {
            return $query->whereDate('created_at', '>=', $startDate->format('Y-m-d H:i:s'))
                ->get();
        }

        //Only end date is specified
        if (is_null($startDate) && !is_null($endDate)) {
            return $query->whereDate('created_at', '<=', $endDate->format('Y-m-d H:i:s'))
                ->get();
        }

        return $query
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

}
