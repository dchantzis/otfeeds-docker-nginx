<?php


namespace App\Http;

use App\Environments;
use App\Interfaces\ApiAuditLogInterface;
use App\Models\ApiAuditLog;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Exception;
use SodiumException;

class ApiResponse
{

    /**
     * @var \Illuminate\Contracts\Foundation\Application|mixed
     */
    private $request;

    /**
     * @var mixed
     */
    private $content;

    /**
     * @var int
     */
    private $httpStatus;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var |null
     */
    private $requestFormat;

    /**
     * @var int
     */
    private $options;

    /**
     * @var Exception
     */
    private $exception;

    /** Declare endpoints (from api.php) that we don't want to store
     *  the full response content in the feeds_api_audit_log
     * @var array
     */
    private $endpointsDontStoreApiAuditLogContent = [
        'iptrace',
        'apiauditlog',
        'systemlogs'
    ];

    /**
     * ApiResponse constructor.
     *
     * @param $data
     * @param int $httpStatus
     * @param \Exception $exception
     * @param array $headers
     * @param int $options
     */
    public function __construct($data, $httpStatus = Response::HTTP_OK, Exception $exception = null, $headers = [], $options = 0)
    {
        $this->request = app(Request::class);
        $this->requestFormat = $this->request->route()->ext ?? null;

        $this->content = $this->addDebugInformation($data, $exception);

        $this->httpStatus = $httpStatus;
        $this->headers = $headers;
        $this->options = $options;
        $this->exception = $exception;
    }

    /**
     * Create Api Audit Log entry for the RESPONSE
     * The corresponding REQUEST entry was created in App\Http\Middleware\InitApiAuditLogRequestEntry
     *
     * @param Request $request
     * @param $content
     * @param Exception $exception
     */
    private function createApiAuditLogResponseEntry(Request $request, $content, Exception $exception = null)
    {
        if(!in_array('init-api-audit-log-request-entry', $request->route()->computedMiddleware)) {
            return;
        }

        resolve(ApiAuditLogInterface::class)
            ->initSlim(
            $request,
            (!($exception instanceof Exception) && in_array($request->path(), $this->endpointsDontStoreApiAuditLogContent)) ? ['status' => 'The request was successful.'] : $content,
            ApiAuditLog::RESPONSE
        );
    }

    /**
     * @param Exception $exception
     */
    private function createLogEntry(Exception $exception = null)
    {

        if (($exception instanceof Exception)) {
            return;
        }

        Log::info(
            sprintf('Successfully accessing endpoint [%s]', $this->request->getRequestUri()),
            (auth()->user()) ? ["Consumer Company" => auth()->user()->company_name, "ID" => auth()->user()->id] : []
        );

    }

    /**
     * Handle the API Responses for the xml or json formats
     *  Additionally, create the RESPONSE entry in feeds_api_audit_log
     *  and an entry in system logs (feeds_system_logs)
     *
     * @return XmlResponse|\Illuminate\Http\JsonResponse
     */
    public function toResponse()
    {

        $this->createApiAuditLogResponseEntry($this->request, $this->content, $this->exception);

        $this->createLogEntry($this->exception);

        if ('xml' === $this->requestFormat) {
            return new XmlResponse(
                $this->content,
                $this->httpStatus,
                $this->headers
            );
        }

        // Default API response is JSON
        return response()->json(
            $this->content,
            $this->httpStatus,
            $this->headers,
            $this->options
        );
    }

    /**
     * @param array|SimpleXMLElement $content
     * @param null $exception
     *
     * @return mixed
     */
    public function addDebugInformation($content, $exception = null)
    {

        if ( Environments::PRODUCTION === strtolower(config('app.env')) || false === config('app.debug') ) {
            return $content;
        }

        $requestFormat = $this->request->route()->ext ?? null;

        $debugInfo = $this->makeDebugInformation($this->request, $exception);

        if ('xml' === $requestFormat && $content instanceof SimpleXMLElement) {
            arrayToXml($content, ['debug' => $debugInfo]);
        } else {

            if (!is_array($content)) {
                $content = [
                  'message' => $content
                ];
            }

            $content['debug'] = $debugInfo;
        }

        return $content;
    }

    /**
     * @param Request $request
     * @param \Exception|null $exception
     * @return array
     */
    protected function makeDebugInformation(Request $request, \Exception $exception = null)
    {
        $requestFormat = $request->route()->ext ?? null;

        $debugInformation = [
            'uri' => $request->path(),
            'request_format' => $requestFormat,
            'request_body' => $request->all(),
        ];

        if ($exception) {

            $message = $exception->getMessage();
            if ($exception instanceof SodiumException) {
                $message = sprintf(
                    '%s - Make sure to generate the .env parameters through the console command
                    `ot:generate-app-keys` for APP_PRIVATE_KEY and APP_INDEX_KEY',
                            $exception->getMessage()
                );
            }

            $debugInformation = array_merge($debugInformation, [
                'message' => $message,
                'code' => $exception->getCode(),
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })
            ]);

        }

        if (config('app.debug_queries')) {

            $debugInformation['queries'] = [];

            foreach (DB::connection()->getQueryLog() as $log)
            {
                array_push(
                    $debugInformation['queries'],
                    [
                        'query' => $this->constructQueryWithBindings($log['query'], $log['bindings']),
                        'time'  => sprintf('%sms', $log['time']),
                    ]
                );
            }

            if ($exception instanceof QueryException) {
                array_push(
                    $debugInformation['queries'],
                    [
                        'query' => $this->constructQueryWithBindings($exception->getSql(), $exception->getBindings()),
                        'time' => '',
                    ]
                );
            }
        }

        return $debugInformation;
    }

    /**
     * @param $query
     * @param $bindings
     * @return string
     */
    protected function constructQueryWithBindings($query, $bindings)
    {
        $bindings = collect($bindings)->map(function ($element) {
            return str_replace('"', '', $element);
        })->toArray();

        return vsprintf(
            str_replace('?', "'%s'", $query),
            $bindings
        );
    }

}
