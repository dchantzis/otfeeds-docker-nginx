<?php

namespace App\Console\Commands;

use App\Interfaces\ApiAuditLogInterface;
use Carbon\Carbon;

class PurgeApiAuditLogEntries extends PurgeLogEntries
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ot:purge-api-audit-log-entries
    {--months= : When specified all entries older than this number of months will be updates. Defaults to .env attribute API_AUDIT_LOG_NUM_MONTHS_PURGE }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the `content` and `meta columns from all entries older than x months';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ApiAuditLogInterface $apiAuditLogRepository)
    {
        $this->logRepository = $apiAuditLogRepository;

        $this->monthsDefault = config('app.api_audit_log_num_months_purge');

        parent::__construct();
    }

    /**
     * @param Carbon $endDate
     * @return mixed
     */
    protected function massUpdateEntriesContent(Carbon $endDate)
    {
        $nowDate = Carbon::now();

        return $this->logRepository->massUpdateEntriesContent(
            $endDate,
            ['info' => sprintf('Content was purged on [%s]', $nowDate->format('Y-m-d H:i:s'))],
            ['purge_date' => $nowDate->format('Y-m-d H:i:s')]
        );
    }

    protected function massDeleteEntries($endDate)
    {
        return 0;
    }

}
