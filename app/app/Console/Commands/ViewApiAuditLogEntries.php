<?php

namespace App\Console\Commands;

use App\Interfaces\ApiAuditLogInterface;
use App\Interfaces\IPTraceInterface;
use App\Models\ApiAuditLog;
use App\Models\IPTrace;
use Illuminate\Console\Command;
use Exception;

class ViewApiAuditLogEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ot:view-api-audit-log-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View entries from the feeds_api_audit_log table';

    /**
     * @var ApiAuditLogInterface
     */
    protected $apiAuditLogRepository;

    /**
     * @var IPTraceInterface
     */
    protected $ipTraceRepository;

    /**
     * ViewApiAuditLogEntries constructor.
     *
     * @param ApiAuditLogInterface $apiAuditLogRepository
     * @param IPTraceInterface $ipTraceRepository
     */
    public function __construct(ApiAuditLogInterface $apiAuditLogRepository, IPTraceInterface $ipTraceRepository)
    {

        $this->apiAuditLogRepository = $apiAuditLogRepository;

        $this->ipTraceRepository = $ipTraceRepository;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        do {

            $ipTraceId = $this->ask('Enter the IP Trace id');

            try {

                $ipTraceEntry = $this->ipTraceRepository->find($ipTraceId);

                if (!$ipTraceEntry) {
                    throw new Exception(sprintf('Unable to find IP Trace entry for id [%s]', $ipTraceId));
                }

                $this->outputIpTraceEntryDetails($ipTraceEntry);

                $this->findApiAuditLogEntriesByIpTrace($ipTraceEntry->id);

            } catch (Exception $exception) {
                $this->line('');
                $this->warn($exception->getMessage());
            }

        } while (('yes' === $this->choice('Enter new IP Trace id?', ['no', 'yes'])) ? true : false);
    }

    /**
     * @param IPTrace $ipTrace
     */
    private function outputIpTraceEntryDetails(IPTrace $ipTrace)
    {

        $this->info(
            sprintf('Showing entry [%s] from table [%s]', $ipTrace->id, $ipTrace->getTable())
        );

        $this->info(sprintf(
            '<comment>Created At</comment>: %s', $ipTrace->created_at
        ));
        $this->info(sprintf(
            '<comment>Updated At</comment>: %s', $ipTrace->updated_at
        ));
        $this->line(sprintf(
            '<comment>IP Address</comment>: %s', $ipTrace->ip_address
        ));

        // Calls to 3ev-Internal endpoints do not have a consumer
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

    }

    /**
     * @param $ipTraceId
     * @return bool
     * @throws Exception
     */
    private function findApiAuditLogEntriesByIpTrace($ipTraceId)
    {

        $apiAuditLogEntries = $this->apiAuditLogRepository->findByIpTraceId($ipTraceId);

        if (0 === count($apiAuditLogEntries)) {
            throw new Exception(sprintf('Unable to find [%s] entries for Ip Trace [%s]', ApiAuditLog::getTableName(), $ipTraceId));
        }

        $list = [
            'exit',
        ];
        foreach ($apiAuditLogEntries as $entry) {
            array_push($list, sprintf('%s (%s)', $entry->id, $entry->type));
        }

        do {

            $apiAuditLogChoice = $this->choice('Select api audit log entry to view', $list);

            if ('exit' === $apiAuditLogChoice) {
                return true;
            }

            list($id, $type) = explode(' ', $apiAuditLogChoice);

            $this->outputApiAuditLogDetails($apiAuditLogEntries->find($id));

        } while (1);

    }

    /**
     * @param ApiAuditLog $apiAuditLog
     */
    private function outputApiAuditLogDetails(ApiAuditLog $apiAuditLog)
    {

        $this->info('API Audit Log Entry Details');
        $this->line(sprintf(
            '<comment>id</comment>: %s', $apiAuditLog->id
        ));
        $this->line(sprintf(
            '<comment>Created at</comment>: %s', $apiAuditLog->created_at
        ));
        $this->line(sprintf(
            '<comment>Updated at</comment>: %s', $apiAuditLog->updated_at
        ));

        // Calls to Healtnhz endpoints do not have a consumer
        if ($apiAuditLog->consumer_id) {
            $this->line(sprintf(
                '<comment>Consumer ID</comment>: %s', $apiAuditLog->consumer_id
            ));

            $this->line(sprintf(
                '<comment>Consumer Company Name</comment>: %s', $apiAuditLog->consumer->company_name
            ));
        }

        $this->line(sprintf(
            '<comment>Type</comment>: %s', $apiAuditLog->type
        ));

        $this->line(sprintf(
            '<comment>Content</comment>: %s', $apiAuditLog->content
        ));

        $this->line('<comment>Meta</comment>:');

        foreach (json_decode($apiAuditLog->meta, true) as $key => $value)
        {
            $this->line("\t" . sprintf(
                '<comment>%s</comment>: %s', $key, json_encode($value)
            ));
        }

    }

}
