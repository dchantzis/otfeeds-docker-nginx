<?php

namespace App\Console\Commands;

use App\Interfaces\SystemLogInterface;
use App\Models\SystemLog;
use Illuminate\Console\Command;

class ViewSystemLogEntries extends ViewLogEntries
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ot:view-system-log-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View entries from the feeds_system_log table';

    /**
     * ViewIpTraceEntries constructor.
     *
     * @param SystemLogInterface $systemLogRepository
     */
    public function __construct(SystemLogInterface $systemLogRepository)
    {
        $this->logRepository = $systemLogRepository;

        parent::__construct();
    }

    /**
     * @param SystemLog $systemLog
     */
    protected function outputDetails(SystemLog $systemLog)
    {

        $this->info(sprintf(
            '<comment>ID</comment>: %s', $systemLog->id
        ));

        $this->info(sprintf(
            '<comment>Ip Trace ID</comment>: %s', $systemLog->ip_trace_id
        ));

        $this->info(sprintf(
            '<comment>Created At</comment>: %s', $systemLog->created_at
        ));
        $this->info(sprintf(
            '<comment>Updated At</comment>: %s', $systemLog->updated_at
        ));

        $this->line(sprintf(
            '<comment>Level</comment>: %s %s', $systemLog->level, $systemLog->level_name
        ));

        $this->line(sprintf(
            '<comment>Message</comment>: %s', $systemLog->message
        ));

        if (json_decode($systemLog->context))
        {
            foreach (json_decode($systemLog->context, true) as $key => $value)
            {
                $this->line("\t" . sprintf(
                        '<comment>%s</comment>: %s', $key, json_encode($value)
                ));
            }
        }

        $this->line(sprintf(
            '<comment>Channel</comment>: %s', $systemLog->channel
        ));

        $this->line('');

    }
}
