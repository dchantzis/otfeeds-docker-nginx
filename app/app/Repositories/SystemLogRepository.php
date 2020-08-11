<?php


namespace App\Repositories;


use App\Interfaces\SystemLogInterface;
use App\Models\SystemLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Monolog\Logger;

class SystemLogRepository extends AbstractRepository implements SystemLogInterface
{

    public function __construct(SystemLog $model)
    {
        $this->model = $model;

        parent::__construct($model);
    }

    /**
     * @return string
     */
    public function model()
    {
        return SystemLog::class;
    }

    /**
     * @param $message
     * @param $level
     * @param $context
     * @param $channel
     * @return mixed
     */
    public function generate($message, $level, $context, $channel)
    {
        $contextString = (is_array($context)) ? json_encode($context) : $context;

        if ($request = app(Request::class)) {
            $ipTraceId = ($request->ip_trace) ? $request->ip_trace->id : null;
        }

        return $this->getModel()->create([
            'ip_trace_id' => $ipTraceId ?? null,
            'message' => $message,
            'level' => $level,
            'level_name' => strtolower(Logger::getLevelName($level)),
            'context' => $contextString,
            'channel' => $channel,
        ]);
    }

    /**
     * @param int $ipTraceId
     * @param int $limit
     * @param int $offset
     * @param null $startDate
     * @param null $endDate
     * @return mixed
     */
    public function get($ipTraceId = null, $limit = 10, $offset = 0, $startDate = null, $endDate = null)
    {
        $query = $this->getModel()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset);

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

    /**
     * @param Carbon $endDate
     * @param $message
     * @param $context
     * @return mixed
     * @throws \Exception
     */
    public function massUpdateEntriesContext(Carbon $endDate, $message, $context)
    {
        DB::beginTransaction();

        $contextString = (is_array($context)) ? json_encode($context) : $context;

        $contentEncrypted = customEncrypter($contextString);

        $result = $this->getModel()::where('created_at', '<=', $endDate->format('Y-m-d H:i:s'))
            ->whereColumn('created_at', 'updated_at')
            ->update([
                    'context' => $contentEncrypted,
                    'message' => $message
                ]
            );

        DB::commit();

        return $result;
    }

    /**
     * @param Carbon $endDate
     * @return mixed
     */
    public function massDeleteEntries(Carbon $endDate)
    {
        DB::beginTransaction();

        $result = $this->getModel()::where('created_at', '<=', $endDate->format('Y-m-d H:i:s'))
            ->where('level', Logger::DEBUG)
            ->delete();

        DB::commit();

        return $result;
    }

}
