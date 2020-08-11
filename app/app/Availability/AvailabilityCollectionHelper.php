<?php

namespace App\Availability;

use Carbon\Carbon;
use DatePeriod;
use DateInterval;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Convenience class for Availability Collection related functions
 */
class AvailabilityCollectionHelper
{

    /**
     * Returns an AvailabilityCollection containing Availability made from each date within each Availability of a given AvailabilityCollection
     *
     * @param $avColData
     * @param bool $includeStart
     * @param bool $includeEnd
     * @return array
     * @throws Exception
     */
    public function splitToSingleDatesArray($avColData, $includeStart = true, $includeEnd = true)
    {
        $returnArr = [];

        foreach ($avColData as $availability) {
            /** @var AvailabilityPeriod $availability */
            $splitColl = $availability->convertToSingleDateAvailabilityCollection($includeStart, $includeEnd);

            foreach ($splitColl as $date => $av) {
                if(array_key_exists($date, $returnArr) && !$returnArr[$date]->isBookable()) {
                    // This date has been already been mapped and told to be not bookable.
                    continue;
                }

                $returnArr[$date] = $av;
            }
        }

        return $returnArr;
    }

    /**
     * Classifies a given set of AvailabilityPeriod models based on if dates within the AvailabilityPeriod models only
     * allow weekly block bookings
     *
     * @param $id
     * @param $splitColl
     * @return array
     * @throws Exception
     */
    public function classifyWeeklyBlocksOnly($id, $splitColl)
    {
        $returnSplitColl = [];
        $weeklyBlockDates = $this->getWeeklyBlocks($id);

        foreach($splitColl as $key => $period){
            $period->setWeeklyBlocksOnly($this->priceRowHasWeeklyBlocksOnlyRestriction($weeklyBlockDates, $key));
            $returnSplitColl[] = $period;
        }

        return $returnSplitColl;
    }

    /**
     * Checks if a given date is marked as only allowing weekly block bookings
     *
     * @param $dwellingId
     *
     * @return array
     * @throws Exception
     */
    public function getWeeklyBlocks($dwellingId)
    {
        $priceRows = DB::select('
            SELECT *
            FROM `dwelling_price_rows`
            WHERE `dwelling_id` = ' . $dwellingId . '
            ORDER BY `on_sale_from`
        ');

        $end = new Carbon();
        $end->addDays(config('app.availability_days', 365));
        $blocks = [];
        $start = null;

        for($i = 0; $i < count($priceRows); $i++) {
            if ($priceRows[$i]->weekly_blocks_only) {
                $onSaleFrom = new Carbon($priceRows[$i]->on_sale_from);
                if (isset($priceRows[$i + 1])) {
                    $onSaleTo = new Carbon($priceRows[$i + 1]->on_sale_from);
                    $onSaleTo->subDay();
                } else {
                    $onSaleTo = $end;
                }

                $blocks[] = array(
                    'start' => $onSaleFrom,
                    'end' => $onSaleTo
                );
            }
        }

        return $blocks;
    }

    /**
     * Checks if a given date is marked as only allowing weekly block bookings
     *
     * @param $weeklyBlockDates
     * @param $date
     *
     * @return bool
     * @throws Exception
     */
    public function priceRowHasWeeklyBlocksOnlyRestriction($weeklyBlockDates, $date)
    {
        $date = new Carbon($date);
        foreach ($weeklyBlockDates as $block) {
            if ($date->between($block['start'], $block['end'])) {
                return true;
            }
        }

        return false;
    }

}
