<?php


namespace App\Interfaces;


use Carbon\Carbon;
use Illuminate\Http\Request;

interface ApiAuditLogInterface
{

    /**
     * @param $ipTraceId
     * @param $content
     * @param null $type
     * @param null $consumerId
     * @param null $meta
     *
     * @return mixed
     */
    public function init($ipTraceId, $content, $type = null, $consumerId = null, $meta = null);

    /**
     * @param Request $request
     * @param $content
     * @param $type
     *
     * @return mixed
     */
    public function initSlim(Request $request, $content, $type);

    /**
     * @param $ipTraceId
     *
     * @return mixed
     */
    public function findByIpTraceId($ipTraceId);

    /**
     * @param Carbon $endDate
     * @param $content
     * @param $meta
     *
     * @return mixed
     */
    public function massUpdateEntriesContent(Carbon $endDate, $content, $meta);

}
