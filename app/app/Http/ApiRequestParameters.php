<?php


namespace App\Http;


class ApiRequestParameters
{

    const LIMIT = 'limit';
    const START_DATE = 'start_date';
    const END_DATE  = 'end_date';
    const IP_TRACE_ID = 'ip_trace_id';

    const HEALTH_DETAILED = 'detailed';

    public static function supported()
    {
        return [
            self::LIMIT,
            self::START_DATE,
            self::END_DATE,
            self::IP_TRACE_ID,
            self::HEALTH_DETAILED,
        ];
    }

}
