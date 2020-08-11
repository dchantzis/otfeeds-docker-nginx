<?php

namespace App\Exceptions;

use App\Environments;
use App\Http\ApiRequestParameters;
use App\Http\XmlResponse;
use App\Models\Dwelling;
use App\Notifications\ExceptionThrown;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $exception
     * @return mixed|void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $exception
     * @return XmlResponse|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response|Response
     */
    public function render($request, Exception $exception)
    {
        switch (true)
        {
            case ($exception instanceof NotFoundHttpException):

                $statusCode = Response::HTTP_NOT_FOUND;
                $message = 'Invalid URL.';
                break;

            case ($exception instanceof ModelNotFoundException &&
                $exception->getModel() === Dwelling::class):

                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
                $message = 'Dwelling ID unknown.';
                break;

            case ($exception instanceof UnauthorisedException):

                $statusCode = Response::HTTP_UNAUTHORIZED;
                $message = 'Invalid access credentials.';
                break;

            case ($exception instanceof UnauthorisedInternalAccessException):

                $statusCode = Response::HTTP_UNAUTHORIZED;
                $message = 'Invalid 3ev Internal Consumer Access';
                break;

            case ($exception instanceof ValidationException):

                $statusCode = Response::HTTP_UNAUTHORIZED;
                $message = 'There are validation errors in the request';
                $meta = $exception->getData();
                break;

            default:
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                $message = 'Server Error. Try again later.';
                break;
        }

        $meta = $meta ?? null;

        // Log the Exception
        self::writeInLog($request, $exception, $message, $statusCode, $meta);

        // Send Slack Notification
        self::sendSlackNotification($request, $exception, $message, $statusCode, $meta);

        $requestFormat = $request->route()->ext ?? null;

        $content = ('xml' === $requestFormat) ? self::buildXmlContent($statusCode, $message, $meta) : self::buildJsonContent($statusCode, $message, $meta);

        return response()->api(
            $content,
            $statusCode,
            $exception
        );

//        return parent::render($request, $exception);
    }

    /**
     * @param Request $request
     * @param Exception $exception
     * @param $message
     * @param $meta
     * @param $statusCode
     */
    protected static function writeInLog(Request $request, Exception $exception, $message, $statusCode, $meta)
    {
        $logContent = [
            'ip_trace_entry_id' => $request->ip_trace->id,
            'api_response_message' => $message,
            'api_response_status_code' => $statusCode,
            'api_response_meta' => json_encode($meta),
            'request_uri' => $request->path(),
            'request_format' => $request->route()->ext ?? null,
            'request_body' => array_filter($request->all(), function($key) {
                return in_array($key, ApiRequestParameters::supported());
            }, ARRAY_FILTER_USE_KEY),
            'exception_message' => ($exception->getMessage()) ? $exception->getMessage() : json_encode($exception->getData()),
            'exception_code' => $exception->getCode(),
            'exception_class' => get_class($exception),
            'filename' => $exception->getFile(),
            'line_number' => $exception->getLine(),
        ];

        try {
            $consumer = auth()->user();
        } catch (Exception $exception) {
            $consumer = null;
        }

        if ($consumer) {
            $logContent = array_merge($logContent, [
                'consumer_id' => $consumer->id,
                'consumer_company_name' => $consumer->company_name
            ]);
        }

        Log::error($message, $logContent);
    }

    /**
     * @param Request $request
     * @param Exception $exception
     * @param $message
     * @param $statusCode
     * @param $meta
     */
    protected static function sendSlackNotification(Request $request, Exception $exception, $message, $statusCode, $meta)
    {

        if (Environments::PRODUCTION !== strtolower(config('app.env')) && false === config('app.debug')) {
            return;
        }

        $logContent = [
            'API Response Message' => $message,
            'API Response Status Code' => $statusCode,
            'API Response Details' => json_encode($meta),

            'URI' => $request->path(),
            'Request Format' => $request->route()->ext ?? null,
            'Request Parameters' => array_filter($request->all(), function($key) {
                return in_array($key, ApiRequestParameters::supported());
            }, ARRAY_FILTER_USE_KEY),

            'Exception Message' => ($exception->getMessage()) ? $exception->getMessage() : json_encode($exception->getData()),
            'Exception Code' => $exception->getCode(),
            'Exception Type' => get_class($exception),

            'Filename' => $exception->getFile(),
            'Line Number' => $exception->getLine(),

            'IP Trace Entry ID' => $request->ip_trace->id,
        ];

        try {
            $consumer = auth()->user();
        } catch (Exception $exception) {
            $consumer = null;
        }

        if ($consumer) {
            $logContent = array_merge($logContent, [
                'Consumer ID' => $consumer->id,
                'Consumer Company Name' => $consumer->company_name
            ]);
        }

        Notification::route('slack', config('logging.channels.slack.url'))
            ->notify(new ExceptionThrown($logContent));
    }

    /**
     * @param $code
     * @param $message
     * @param $meta
     * @return \SimpleXMLElement
     */
    protected static function buildXmlContent($code, $message, $meta)
    {
        $xml = new \SimpleXMLElement('<Error></Error>');
        $xml->addChild('Code', $code);
        $xml->addChild('Message', $message);

        if ($meta) {
            arrayToXml($xml, ['details' => $meta]);
        }

        return $xml;
    }

    /**
     * @param $code
     * @param $message
     * @param $meta
     * @return array
     */
    protected static function buildJsonContent($code, $message, $meta)
    {

        $responseData = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ]
        ];

        if ($meta) {
            $responseData = Arr::add($responseData, 'details', $meta);
        }

        return $responseData;
    }

}
