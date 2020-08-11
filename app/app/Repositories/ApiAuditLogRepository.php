<?php


namespace App\Repositories;


use App\Http\ApiRequestParameters;
use App\Interfaces\ApiAuditLogInterface;
use App\Models\ApiAuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiAuditLogRepository extends AbstractRepository implements ApiAuditLogInterface
{

    /**
     * ApiAuditLogRepository constructor.
     *
     * @param ApiAuditLog $model
     */
    public function __construct(ApiAuditLog $model)
    {
        $this->model = $model;

        parent::__construct($model);
    }

    /**
     * @return string
     */
    public function model()
    {
        return ApiAuditLog::class;
    }

    /**
     * @param $ipTraceId
     * @param $content
     * @param null $type
     * @param null $consumerId
     * @param null $meta
     * @return mixed
     */
    public function init($ipTraceId, $content, $type = null, $consumerId = null, $meta = null)
    {
        DB::beginTransaction();

        $contentString = (is_array($content) || $content instanceof \SimpleXMLElement) ? json_encode($content) : $content;
        $metaString = (is_array($meta) || $meta instanceof \SimpleXMLElement) ? json_encode($meta) : $meta;

        $auditLogEntry = $this->getModel()::create([
            'ip_trace_id'   => $ipTraceId,
            'consumer_id'   => $consumerId,
            'content'       => $contentString,
            'type'          => $type,
            'meta'          => $metaString,
        ]);

        DB::commit();

        return $auditLogEntry;
    }

    /**
     * @param Request $request
     * @param $content
     * @param $type
     * @return mixed
     */
    public function initSlim(Request $request, $content, $type)
    {
        $meta = [
            'headers' => $request->header(),
            'path' => $request->path(),
            'route_parameters' => array_filter($request->all(), function($key) {
                return in_array($key, ApiRequestParameters::supported());
            }, ARRAY_FILTER_USE_KEY),
        ];

        $consumerId = null;
        if ($consumer = auth()->user()) {
            $consumerId = $consumer->id;
        }

        return $this->init(
          $request->ip_trace->id,
          $content,
          $type,
          $consumerId,
          $meta
        );

    }

    /**
     * @param $ipTraceId
     *
     * @return mixed
     */
    public function findByIpTraceId($ipTraceId)
    {
        return $this->getModel()::where('ip_trace_id', $ipTraceId)
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * @param Carbon $endDate
     * @param $content
     * @param $meta
     * @return mixed
     *
     * @throws \Exception
     */
    public function massUpdateEntriesContent(Carbon $endDate, $content, $meta)
    {
        DB::beginTransaction();

        $contentString = (is_array($content) || $content instanceof \SimpleXMLElement) ? json_encode($content) : $content;
        $metaString = (is_array($meta) || $meta instanceof \SimpleXMLElement) ? json_encode($meta) : $meta;

        $contentEncrypted = customEncrypter($contentString);
        $metaEncrypted = customEncrypter($metaString);

        $result = $this->getModel()::where('created_at', '<=', $endDate->format('Y-m-d H:i:s'))
            ->whereColumn('created_at', 'updated_at')
            ->update([
                'content' => $contentEncrypted,
                'meta' => $metaEncrypted
            ]);

        DB::commit();

        return $result;
    }

}
