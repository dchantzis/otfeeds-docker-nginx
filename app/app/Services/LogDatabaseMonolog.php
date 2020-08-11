<?php


namespace App\Services;


use App\Handlers\LogDatabaseMonologHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

class LogDatabaseMonolog
{

    public function __invoke(array $config)
    {
        $logger = new Logger(config('logging.default'));
        $pdoHandler = new LogDatabaseMonologHandler();

        $formatter = new LineFormatter('%message%');
        $formatter->includeStacktraces(true);
        $pdoHandler->setFormatter($formatter);

        $logger->pushHandler($pdoHandler);
        $logger->pushProcessor(new PsrLogMessageProcessor());

        return $logger;
    }

}
