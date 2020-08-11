<?php


namespace App\Console\Commands;

use App\Interfaces\IPTraceInterface;
use App\Interfaces\SystemLogInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

abstract class ViewLogEntries extends Command
{

    const VIEW_TYPE_IP_TRADE_ID = 'IP Trace ID';
    const VIEW_TYPE_DATE_RANGE = 'Date range';
    const VIEW_TYPE_MOST_RECENT = 'Most recent';

    /**
     * @var IPTraceInterface|SystemLogInterface
     */
    protected $logRepository;

    /**
     * @var Carbon
     */
    protected $startDate;

    /**
     * @var Carbon
     */
    protected $endDate;

    /**
     * @throws \Exception
     */
    public function handle()
    {

        $viewType = $this->choice(
          'Select view type',
          [
              self::VIEW_TYPE_IP_TRADE_ID,
              self::VIEW_TYPE_DATE_RANGE,
              self::VIEW_TYPE_MOST_RECENT,
          ]
        );

        $ipTraceId = (self::VIEW_TYPE_IP_TRADE_ID === $viewType) ? (int)$this->ask('Enter IP Trace ID') : null;

        if (self::VIEW_TYPE_DATE_RANGE === $viewType) {
            $this->makeDateDuration();
        }

        $numberOfResults = $this->numberOfResults();

        $offset = 0;
        $page = 1;

        do {

            $results = $this->logRepository->get($ipTraceId, $numberOfResults, $offset, $this->startDate, $this->endDate);

            if (0 == $results->count()) {
                $this->comment('End of results.');
                $executeAgain = false;

                continue;
            }

            foreach ($results as $entry)
            {
                $this->outputDetails($entry);
            }

            $executeAgain = ('yes' === $this->choice(
                sprintf('View next %s?', $numberOfResults),
                ['no', 'yes'],
                'yes'
            )) ? true : false;
            ++$page;

            $offset = ($page * $numberOfResults) - $numberOfResults;

        } while ($executeAgain);

        $this->comment('Exiting.');

    }

    /**
     * @throws \Exception
     */
    protected function makeDateDuration()
    {

        $startDate = $this->ask('Start Date [YYYY-MM-DD HH:MM:SS]');
        $endDate = $this->ask('End Date [YYYY-MM-DD HH:MM:SS]');

        $startDate = str_replace('/', '-', $startDate);
        $endDate = str_replace('/', '-', $endDate);

        if (!$startDate && !$endDate) {
            return $this->calculateDateDuration();
        }

        $this->startDate = new Carbon($startDate);
        if ('00:00:00' === $this->startDate->format('H:i:s')) {
            $this->startDate->startOfDay();
        }

        $this->endDate = new Carbon($endDate);
        if('00:00:00' === $this->endDate->format('H:i:s')) {
            $this->endDate->endOfDay();
        }

        if (1 > $this->startDate->diffInDays($this->endDate)) {
            $this->endDate = clone $this->startDate;
            $this->endDate->endOfDay();
        }
    }

    /**
     * @throws \Exception
     */
    private function calculateDateDuration()
    {
        $this->startDate = new Carbon('-1 week');
        $this->startDate->startOfMonth();
        $this->startDate->startOfDay();

        $this->endDate = new Carbon('-1 week');
        $this->endDate->endOfMonth();
        $this->endDate->endOfDay();
    }

    /**
     * @return int
     */
    protected function numberOfResults()
    {
        $numberOfResults = (int)$this->ask('Number of entries to view (Desc). Maximum 20', 10);

        if (0 > $numberOfResults || 20 < $numberOfResults) {
            $numberOfResults = 10;
        }

        return $numberOfResults;
    }

}
