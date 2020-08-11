<?php


namespace App\Interfaces;

/**
 * Interface IPTraceInterface
 * @package App\Interfaces
 */
interface IPTraceInterface
{

    /**
     * @param $ipAddress
     * @param $consumerId
     * @param $requestMethod
     * @param $route
     * @param $requestParameters
     * @param $requestHeader
     * @param $host
     * @return mixed
     */
    public function generate($ipAddress, $consumerId, $requestMethod, $route, $requestParameters, $requestHeader, $host);

    /**
     * @param null $ipTraceId
     * @param int $limit
     * @param int $offset
     * @param null $startDate
     * @param null $endDate
     * @return mixed
     */
    public function get($ipTraceId = null, $limit = 10, $offset = 0, $startDate = null, $endDate = null);

}
