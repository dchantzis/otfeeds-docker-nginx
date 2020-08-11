<?php

namespace App\Handlers;

use App\Events\LogMonologEvent;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class LogDatabaseMonologHandler extends AbstractProcessingHandler
{

    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(array $record) : void
    {
        event(new LogMonologEvent($record));
    }

}
