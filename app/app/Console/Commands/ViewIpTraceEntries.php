<?php

namespace App\Console\Commands;

use App\Interfaces\IPTraceInterface;
use App\Models\IPTrace;

class ViewIpTraceEntries extends ViewLogEntries
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ot:view-ip-trace-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View entries from the feeds_ip_trace table';


    /**
     * ViewIpTraceEntries constructor.
     *
     * @param IPTraceInterface $ipTraceRepository
     */
    public function __construct(IPTraceInterface $ipTraceRepository)
    {
        $this->logRepository = $ipTraceRepository;

        parent::__construct();
    }

    /**
     * @param IPTrace $ipTrace
     */
    protected function outputDetails(IPTrace $ipTrace)
    {

        $this->info(sprintf(
            '<comment>ID</comment>: %s', $ipTrace->id
        ));

        $this->info(sprintf(
           '<comment>Created At</comment>: %s', $ipTrace->created_at
        ));

        $this->info(sprintf(
            '<comment>Updated At</comment>: %s', $ipTrace->updated_at
        ));

        $this->line(sprintf(
           '<comment>IP Address</comment>: %s', $ipTrace->ip_address
        ));

        // Calls to Healthz endpoints do not have a consumer
        if ($ipTrace->consumer_id) {
            $this->line(sprintf(
                '<comment>Consumer ID</comment>: %s', $ipTrace->consumer_id
            ));

            $this->line(sprintf(
                '<comment>Consumer Company Name</comment>: %s', $ipTrace->consumer->company_name
            ));
        }

        $this->line(sprintf(
            '<comment>Request Method</comment>: %s', $ipTrace->request_method
        ));

        $this->line(sprintf(
            '<comment>Route</comment>: %s', $ipTrace->route
        ));

        $this->line(sprintf(
           '<comment>Request Parameters</comment>: %s', $ipTrace->request_parameters
        ));

        $this->line(sprintf(
            '<comment>Request Header</comment>: %s', $ipTrace->request_header
        ));

        $this->line(sprintf(
            '<comment>Host</comment>: %s', $ipTrace->host
        ));

        $this->line('');

    }
}
