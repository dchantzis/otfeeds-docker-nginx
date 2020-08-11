<?php

namespace App\Console\Commands;

use App\Interfaces\ApiAuditLogInterface;
use App\Interfaces\SystemLogInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Exception;

abstract class PurgeLogEntries extends Command
{

    /**
     * @var ApiAuditLogInterface|SystemLogInterface
     */
    protected $logRepository;

    /**
     * @var integer
     */
    protected $monthsDefault;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Carbon $endDate
     * @return mixed
     */
    protected abstract function massUpdateEntriesContent(Carbon $endDate);

    /**
     * @param Carbon $endDate
     * @return mixed
     */
    protected abstract function massDeleteEntries(Carbon $endDate);

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $months = $this->makeMonthsParameter();

        $endDate = Carbon::now()->subMonths($months)->endOfDay();

        $deleteResult = $this->massDeleteEntries($endDate);

        $updateResult = $this->massUpdateEntriesContent($endDate);

        $this->line('');
        $this->line(sprintf(
            '<comment>Updated all entries before</comment>: <info>%s</info> (now - <info>%s</info> months)', $endDate->format('Y-m-d H:i:s'), $months)
        );
        $this->line(sprintf(
            '<comment>Number of entries updated</comment>: <info>%s</info>', $updateResult
        ));
        $this->line(sprintf(
            '<comment>Number of entries deleted</comment>: <info>%s</info>', $deleteResult
        ));

        $this->line('');

        $this->comment('Exiting.');
    }

    /**
     * @return int|null
     */
    protected function makeMonthsParameter()
    {

        $months = (int)$this->option('months');

        do {

            if (!$months){
                $months = (int)$this->ask('Enter months value (greater than <comment>1</comment>)');
            }

            try {
                if ($months <= 1) {
                    throw new Exception(
                        sprintf('Invalid months value %s. Please enter an integer greater than 1', $months)
                    );
                }

                $retry = null;
            } catch (Exception $exception) {
                $this->line('');
                $this->warn($exception->getMessage());

                $retry = $this->choice('Invalid months value. Retry?', [
                    'yes',
                    sprintf('use default months = %s', $this->monthsDefault)
                ]);

                $months = ('yes' !== $retry) ? $this->monthsDefault : null;

            }

        } while('yes' === $retry ? true : false);

        return $months;
    }

}
