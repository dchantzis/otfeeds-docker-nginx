<?php

namespace App\Console\Commands;

use App\Interfaces\SystemLogInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PurgeSystemLogEntries extends PurgeLogEntries
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ot:purge-system-log-entries
        {--months= : When specified all entries older than this number of months will be updates. Defaults to .env attribute SYSTEM_LOG_NUM_MONTHS_PURGE }';

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
    public function __construct(SystemLogInterface $systemLogInterface)
    {
        $this->logRepository = $systemLogInterface;

        $this->monthsDefault = config('app.system_log_num_months_purge');

        parent::__construct();
    }

    /**
     * @param Carbon $endDate
     * @return mixed
     */
    protected function massUpdateEntriesContent(Carbon $endDate)
    {
        $nowDate = Carbon::now();

        return $this->logRepository->massUpdateEntriesContext(
            $endDate,
            sprintf('Content was purged on [%s]', $nowDate->format('Y-m-d H:i:s')),
            ['purge_date' => $nowDate->format('Y-m-d H:i:s')]
        );
    }

    /**
     * @param Carbon $endDate
     * @return mixed
     */
    protected function massDeleteEntries($endDate)
    {
        return $this->logRepository->massDeleteEntries($endDate);
    }

}
