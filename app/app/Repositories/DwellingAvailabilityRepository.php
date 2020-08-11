<?php


namespace App\Repositories;

use App\Availability\AvailabilityCollection;
use App\Availability\AvailabilityPeriod;
use App\Models\Dwelling;
use App\Repositories\Contracts\DwellingAvailabilityRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use League\Period\Period;

class DwellingAvailabilityRepository implements DwellingAvailabilityRepositoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function findForDwelling($id, $duration = 365)
    {
        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addDays($duration);
        /** @var Dwelling $dwelling */
        $dwelling = Dwelling::saleable()->findOrFail($id);
        $relatedDwellingIds = $this->findRelatedDwellingIds($id);
        $dwellingIds = array_merge(
            [$id],
            $relatedDwellingIds->toArray()
        );
        $unavailableDates = $this->findUnavailableDates($dwellingIds, $startDate, $endDate);

        return $this->buildAvailabilityCollection($unavailableDates);
    }

    /**
     * @param int $parentId The primary key of the parent dwelling.
     * @return mixed
     */
    private function findRelatedDwellingIds($parentId)
    {
        return \DB::table('dwellings_related_dwellings')
            ->select('related_dwelling_id')
            ->join('dwellings', 'dwellings.id', '=', 'dwellings_related_dwellings.dwelling_id')
            ->where('dwellings.sale_state', Dwelling::SALE_STATE_ON_SALE)
            ->where('dwelling_id', '=', $parentId)
            ->get();
//            ->lists('related_dwelling_id');
    }

    /**
     * @param int[] $dwellingIds The dwelling IDs to check availability for.
     * @param Carbon $startDate The start date to check.
     * @param Carbon $endDate The end date to check.
     * @return \stdClass[] An array of objects with sale_date and end_date properties with dates in the format Y-m-d.
     */
    private function findUnavailableDates(array $dwellingIds, Carbon $startDate, Carbon $endDate)
    {
        $stopDates = $this->findSaleStopDates($dwellingIds, $startDate, $endDate);
        $bookedDates = $this->findBookedDates($dwellingIds, $startDate, $endDate);

        return array_merge(
            $stopDates->toArray(),
            $bookedDates->toArray()
        );
    }

    /**
     * @param int[] $dwellingIds The dwelling IDs to check availability for.
     * @param Carbon $startDate The start date to check.
     * @param Carbon $endDate The end date to check.
     * @return \stdClass[] An array of objects with sale_date and end_date properties with dates in the format Y-m-d.
     */
    private function findSaleStopDates(array $dwellingIds, Carbon $startDate, Carbon $endDate)
    {
        return DB::table('dwelling_stop_sale_dates')
            ->select('start_date', 'end_date')
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->whereIn('dwelling_id', $dwellingIds)
            ->orderBy('start_date')
            ->get();
    }

    /**
     * @param int[] $dwellingIds The dwelling IDs to check availability for.
     * @param Carbon $startDate The start date to check.
     * @param Carbon $endDate The end date to check.
     * @return \stdClass[] An array of objects with sale_date and end_date properties with dates in the format Y-m-d.
     */
    private function findBookedDates(array $dwellingIds, Carbon $startDate, Carbon $endDate)
    {
        return \DB::table('booking_lines')
            ->select('bookings.start_date', 'bookings.end_date')
            ->distinct()
            ->join('bookings', 'bookings.id', '=', 'booking_lines.booking_id')
            ->whereNotIn('bookings.status', $this->getUnconfirmedBookingStatusIds())
            ->where('bookings.start_date', '<=', $endDate)
            ->where('bookings.end_date', '>=', $startDate)
            ->whereIn('bookings.dwelling_id', $dwellingIds)
            ->orderBy('bookings.start_date')
            ->get();
    }

    /**
     * Build a populated {@see AvailabilityCollection} based on the unavailable dates queried previously.
     *
     * @param array $unavailableDates Array of objects with stat_date and end_date properties.
     * @return AvailabilityCollection
     * @throws \League\Period\Exception
     */
    private function buildAvailabilityCollection($unavailableDates)
    {
        $availability = new AvailabilityCollection();

        foreach ($unavailableDates as $dates) {
            $availability->addAvailabilityPeriod(new AvailabilityPeriod(new Period($dates->start_date, $dates->end_date), false));
        }

        return $availability;
    }

    /**
     * @return array
     */
    private function getUnconfirmedBookingStatusIds()
    {
        return array(
            1001, // Expired
            1002, // Manually cancelled
            1003, // Cancelled and funds retaine
            1004, // Cancelled and rearranged
            1005  // Cancelled and 'held'.
        );
    }

    private function mergeStopDates($stopDates, $duration = 1095) {

        $dateRangeArray = array();
        $currentDay = date("Y-m-d");
        $currentDay = new \DateTime($currentDay);

        // Create array of dates for the year ahead.
        for ($x = 0; $x <= $duration; $x++) {
            $dateRangeArray[$currentDay->format("Y-m-d")] = 0;
            $currentDay = date_add($currentDay, date_interval_create_from_date_string('1 day'));
        }

        foreach ($stopDates as $stopDate) {
            $startDate = new \DateTime($stopDate->start_date);
            $endDate = new \DateTime($stopDate->end_date);

            while ($startDate != $endDate ) {
                $dateRangeArray[$startDate->format('Y-m-d')] = 1;
                $startDate = date_add($startDate, date_interval_create_from_date_string('1 day'));
            }
        }

        //Turn it into a formatted array
        $dateRangeArray = $this->formatStopSales($dateRangeArray);

        return $dateRangeArray;
    }

    private function formatStopSales($dateRangeArray){
        // Take the array
        $stopSales = array();

        $startDate = null;
        $endDate = null;
        $startSet = false;

        $stopSalePeriod = new \stdClass();

        foreach ($dateRangeArray as $date => $stopSale) {
            if ($stopSale == 1) {
                if (!$startSet) {
                    $stopSalePeriod->start_date = $date;
                    $startSet = true;
                }
            } else {
                if ($startSet) {
                    $stopSalePeriod->end_date = $date;
                    $stopSales[] = $stopSalePeriod;
                    $startSet = false;
                }
            }
        }

        if (!isset($stopSalePeriod->end_date)) {
            end($dateRangeArray);
            $stopSalePeriod->end_date = key($dateRangeArray);
            $stopSales[] = $stopSalePeriod;
        }

        return $stopSales;
    }

}
