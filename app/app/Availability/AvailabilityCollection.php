<?php

namespace App\Availability;

use Carbon\Carbon;
use League\Period\Period;
use App\Availability\AvailabilityPeriod;

class AvailabilityCollection implements \IteratorAggregate, \ArrayAccess

{

    /**
     * @var AvailabilityPeriod[]
     */
    private $periods = [];

    public function addAvailabilityPeriod(AvailabilityPeriod $period)
    {
        // Check whether a matching availability period has already been added
        foreach ($this->periods as $existing) {
            // We've passed the start date that would match so continue adding the availability period.
            if ($existing->getStartDate() < $period->getStartDate()) {
                break;
            }

            if ($existing->isEqual($period)) {
                return $this;
            }
        }

        $this->periods[] = $period;

        return $this;

    }

    /**
     * @return AvailabilityPeriod[]
     */
    public function getAvailabilityPeriods()
    {
        return $this->periods;
    }

    /**
     * Sort the periods based on their start and end dates.
     */
    private function sortPeriods()
    {
        // Suppress a spurious error message. See https://github.com/phpspec/prophecy/issues/161
        @usort($this->periods, function (AvailabilityPeriod $a, AvailabilityPeriod $b) {
            if ($a->endIsEqual($b)) {
                return $a->getStartDate()->date < $b->getStartDate()->date ? -1 : 1;
            }
            return $a->isBefore($b) ? -1 : 1;
        });
    }

    /**
     * Merges together contiguous date periods for availability that has the same status and same weekly_blocks_only value.
     *
     * @param int $fromIndex The index in the periods class array to start from. This allows merging neighboring periods
     *                       and restarting the merge process from that merged period.
     *
     * @throws \League\Period\Exception
     */
    private function mergePeriods($fromIndex = 0)
    {
        // Ensure the periods are sorted to allow valid comparisons.
        $this->sortPeriods();

        $merged = [];
        $start = null;
        $i = null;
        $count = count($this->periods);
        for ($i = $fromIndex; $i < $count - 1; $i++) {
            if ($start === null) {
                $start = $i;
            }

            /** @var AvailabilityPeriod $current */
            $current = $this->periods[$i];

            /** @var AvailabilityPeriod $next */
            $next = $this->periods[$i + 1];

            // Only merge availability with the same state
            if (!($current->isBookable() === $next->isBookable() && $current->isWeeklyBlocksOnly() === $next->isWeeklyBlocksOnly())) {

                $merged[] = new AvailabilityPeriod(
                    new Period(
                        $this->periods[$start]->getStartDate(),
                        $this->periods[$i]->getEndDate()
                    ),
                    $this->periods[$i]->isBookable(),
                    $this->periods[$i]->isWeeklyBlocksOnly()
                );

                $start = null;
            }
        }

        if ($start && $i) {
            $merged[] = new AvailabilityPeriod(
                new Period($this->periods[$start]->getStartDate(), $this->periods[$i]->getEndDate()),
                $this->periods[$i]->isBookable(),
                $this->periods[$i]->isWeeklyBlocksOnly()
            );
        }

        if (count($merged)) {
            $this->periods = $merged;
        }

    }

    /**
     * @param Period $between
     * @return $this
     */
    public function isolatePeriods(Period $between)
    {
        $periods = [];

        foreach ($this->periods as $period) {

            /** @var AvailabilityPeriod $period */

            // Start date is before the cutoff and end date is after the cutoff - trim start and end date
            if ($period->getStartDate() < $between->getStartDate() && $period->getEndDate() > $between->getEndDate()) {
                $periods[] = $period
                    ->startOn($between->getStartDate())
                    ->endOn($between->getEndDate());

                continue;
            }

            // End date is before the cutoff - ignore these dates
            if ($period->getEndDate() < $between->getStartDate()) {
                continue;
            }

            // Start date is after the cutoff - ignore these dates
            if ($period->getStartDate() > $between->getEndDate()) {
                continue;
            }

            // Start date is before the cutoff and end date is within the cutoff - trim start date
            if (
                $period->getStartDate() < $between->getStartDate() &&
                $period->getStartDate() <= $between->getEndDate() &&
                $period->getEndDate() <= $between->getEndDate()
            ) {
                $periods[] = $period->startOn($between->getStartDate());

                continue;
            }

            // Start date is within the cutoff but end date is after the cutoff - trim end date
            if (
                $period->getStartDate() >= $between->getStartDate() &&
                $period->getStartDate() <= $between->getEndDate() &&
                $period->getEndDate() > $between->getEndDate()
            ) {
                $periods[] = $period->endOn($between->getEndDate());

                continue;
            }

            // The dates are within the cutoffs - nothing needs changing.
            $periods[] = $period;
        }

        $this->periods = $periods;

        return $this;

    }

    /**
     * @param Period $period The period to fill the gaps between.
     * @param $available 'Should the generated availability be available or not?
     *
     * @return $this
     * @throws \League\Period\Exception
     */
    public function fillGapsWithAvailability(Period $period, $available)
    {
        $this->sortPeriods();

        $fromDate = $period->getStartDate();
        $toDate = $period->getEndDate();

        if (empty($this->periods)) {
            $availability = new AvailabilityPeriod($period, $available);
            $this->addAvailabilityPeriod($availability);
        }

        foreach ($this->periods as $period) {

            if ($fromDate < $period->getStartDate()) {
                $availability = new AvailabilityPeriod(new Period($fromDate, $period->getStartDate()), $available);
                $this->addAvailabilityPeriod($availability);
            }

            $fromDate = $period->getEndDate();
        }

        if ($period->getEndDate() < $toDate) {
            $availability = new AvailabilityPeriod(new Period($period->getEndDate(), $toDate), $available);
            $this->addAvailabilityPeriod($availability);
        }

        return $this;
    }

    /**
     * Splits AvailabilityCollection AvailabilityPeriod objects into single date AvailabilityPeriod objects, classifies
     * each object by whether the date only allows weekly block bookings or not, then remerges the AvailabilityPeriod objects
     *
     * @param $id
     * @return $this
     * @throws \League\Period\Exception
     */
    public function splitByWeeklyBlocksOnly($id)
    {
        $availabilityCollectionHelper = new  AvailabilityCollectionHelper();

        $splitColl = $availabilityCollectionHelper->splitToSingleDatesArray($this->periods, true, false);

        $classifiedSplitColl = $availabilityCollectionHelper->classifyWeeklyBlocksOnly($id, $splitColl);

        $this->periods = $classifiedSplitColl;

        $this->mergePeriods();

        return $this;
    }


    public static function buildUnavailable(Period $period)
    {
        $collection = new self();
        $collection->addAvailabilityPeriod(new AvailabilityPeriod($period, false));

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->periods);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->periods[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->periods[$offset];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Availability must be added using addAvailabilityPeriod.');
    }

    /**
     * {@inheritdoc}
     *
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException('Availability cannot be deleted.');
    }

}
