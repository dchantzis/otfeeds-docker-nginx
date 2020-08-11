<?php

namespace App\Availability;

use Carbon\Carbon;
use League\Period\Datepoint;
use League\Period\Period;

class AvailabilityPeriod
{

    /**
     * @var Period
     */
    private $period;

    /**
     * @var bool
     */
    private $bookable;

    /**
     * @var
     */
    protected $weeklyBlocksOnly;

    /**
     * AvailabilityPeriod constructor.
     *
     * @param Period $period
     * @param $bookable
     * @param bool $weeklyBlocksOnly
     */
    public function __construct(Period $period, $bookable, $weeklyBlocksOnly = false)
    {
        $this->period = $period;
        $this->bookable = $bookable;
        $this->weeklyBlocksOnly = $weeklyBlocksOnly;
    }

    /**
     * The start date.
     *
     * @return \DateTime|\DateTimeInterface
     */
    public function getStartDate()
    {
        return $this->period->getStartDate();
    }

    /**
     * The end date.
     *
     * @return \DateTime|\DateTimeInterface
     */
    public function getEndDate()
    {
        return $this->period->getEndDate();
    }

    /**
     * Can this period be booked?
     *
     * @return bool
     */
    public function isBookable()
    {
        return $this->bookable;
    }

    /**
     * Does this period end before the comparison one starts?
     *
     * @param AvailabilityPeriod $comparison
     * @return bool
     */
    public function isBefore(AvailabilityPeriod $comparison)
    {
        return $this->period->isBefore($comparison->period);
    }

    /**
     * Does this period abut the comparison?
     *
     * @param AvailabilityPeriod $comparison
     * @return bool
     */
    public function abuts(AvailabilityPeriod $comparison)
    {
        return $this->period->abuts($comparison->period);
    }

    /**
     * Does this period overlaps the comparison?
     *
     * @param AvailabilityPeriod $comparison
     * @return bool
     */
    public function overlaps(AvailabilityPeriod $comparison)
    {
        return $this->period->overlaps($comparison->period);
    }

    /**
     * Are the two objects the same when the values are compared?
     *
     * @param AvailabilityPeriod $comparison
     * @return bool
     */
    public function isEqual(AvailabilityPeriod $comparison)
    {
        return $this->isBookable() === $comparison->isBookable() &&
            $this->getStartDate() == $comparison->getStartDate() &&
            $this->getEndDate() == $comparison->getEndDate();
    }

    /**
     * Does this object end at the same time as the other object?
     *
     * @param AvailabilityPeriod $comparison
     * @return bool
     */
    public function endIsEqual(AvailabilityPeriod $comparison)
    {
        return $this->getEndDate() == $comparison->getEndDate();
    }

    /**
     * Merge two periods if their bookable states are same.
     *
     * @param AvailabilityPeriod $comparison
     * @return AvailabilityPeriod
     */
    public function merge(AvailabilityPeriod $comparison)
    {
        if ($this->isBookable() === $comparison->isBookable() && ($this->abuts($comparison) || $this->overlaps($comparison))) {
            $this->period = $this->period->merge($comparison->period);
        }

        return $this;
    }

    /**
     * Make the period start on the passed date.
     *
     * @param Datepoint $date
     * @return $this
     */
    public function startOn(Datepoint $date)
    {
        $this->period = $this->period->startingOn($date);

        return $this;
    }

    /**
     * Make the period end on the passed date.
     *
     * @param Datepoint $date
     * @return $this
     */
    public function endOn(Datepoint $date)
    {
        $this->period = $this->period->endingOn($date);

        return $this;
    }

    /**
     * Returns  $this->weeklyBlocksOnly
     *
     * @return bool
     */
    public function isWeeklyBlocksOnly()
    {
        return $this->weeklyBlocksOnly;
    }

    /**
     * Sets $this->weeklyBlocksOnly
     *
     * @param bool $weeklyBlocksOnly
     */
    public function setWeeklyBlocksOnly($weeklyBlocksOnly)
    {
        $this->weeklyBlocksOnly = $weeklyBlocksOnly;
    }

    /**
     * @param bool $includeStart
     * @param bool $includeEnd
     * @return array
     * @throws \Exception
     */
    public function convertToSingleDateAvailabilityCollection($includeStart = true, $includeEnd = true)
    {
        $returnArr = array();

        $dateList = $this->getDateList($includeStart, $includeEnd);

        foreach ($dateList as $date) {
            $start = new Carbon($date);
            $end = new Carbon($date);
            $end = $end->addDay();

            $period = new Period($start->format('Y-m-d'), $end->format('Y-m-d'));
            $newAv = new AvailabilityPeriod(
                $period,
                $this->isBookable()
            );

            $returnArr[$date] = $newAv;
        }

        return $returnArr;
    }

    /**
     * Get a list of dates (inclusive), starting at $this->startDate and ending at $this->endDate
     *
     * @param   bool    $includeStart
     * @param   bool    $includeEnd
     *
     * @return  array
     *
     * @throws  \Exception
     */
    public function getDateList($includeStart = true, $includeEnd = true)
    {
        $startDate = clone $this->period->getStartDate();
        if(!$includeStart){
            $startDate = $startDate->modify('+1 day');
        }

        $endDate = clone $this->getEndDate();
        if($includeEnd){
            $endDate = $endDate->modify('+1 day');
        }

        if($startDate->format('Y-m-d') == $endDate->format('Y-m-d')){
            $endDate = $endDate->modify('+1 day');
        }

        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            $endDate
        );

        $returnArr = [];

        foreach($period as $date){
            $returnArr[] = $date->format('Y-m-d');
        }

        return $returnArr;
    }

}
