<?php

namespace App\Interfaces;

use Carbon\Carbon;

interface SystemLogInterface
{
    /**
     * @param $message
     * @param $level
     * @param $context
     * @param $channel
     * @return mixed
     */
    public function generate($message, $level, $context, $channel);

    /**
     * @param int $ipTraceId
     * @param int $limit
     * @param int $offset
     * @param null $startDate
     * @param null $endDate
     * @return mixed
     */
    public function get($ipTraceId = null, $limit = 10, $offset = 0, $startDate = null, $endDate = null);

    /**
     * @param Carbon $endDate
     * @param $message
     * @param $context
     * @return mixed
     */
    public function massUpdateEntriesContext(Carbon $endDate, $message, $context);

    /**
     * @param Carbon $endDate
     * @return mixed
     */
    public function massDeleteEntries(Carbon $endDate);
}
