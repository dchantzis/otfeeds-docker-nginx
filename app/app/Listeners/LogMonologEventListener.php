<?php

namespace App\Listeners;

use App\Interfaces\SystemLogInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class LogMonologEventListener implements ShouldQueue
{

    public $queue = 'logs';

    /**
     * @var SystemLogInterface
     */
    protected $systemLogRepository;

    public function __construct(SystemLogInterface $systemLogRepository)
    {
        $this->systemLogRepository = $systemLogRepository;
    }

    public function handle($events)
    {
        $this->systemLogRepository->generate(
            $events->record['formatted'],
            $events->record['level'],
            json_encode($events->record['context'], JSON_UNESCAPED_UNICODE),
            $events->record['channel']
        );
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\LogMonologEvent',
            'App\Listeners\LogMonologEventListener@handle'
        );
    }

}
