<?php

namespace App\Models;

use Carbon\Carbon;

class DwellingSummary
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var Carbon
     */
    private $bookingSystemUpdatedAt;

    /**
     * @var Carbon
     */
    private $websiteUpdatedAt;

    /**
     * @param int $id
     * @param Carbon $bookingSystemUpdatedAt
     * @param Carbon $websiteUpdatedAt
     */
    public function __construct($id, Carbon $bookingSystemUpdatedAt, Carbon $websiteUpdatedAt)
    {
        $this->id = $id;
        $this->bookingSystemUpdatedAt = $bookingSystemUpdatedAt;
        $this->websiteUpdatedAt = $websiteUpdatedAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * Returns the later of the booking system dwelling updated date and the website dwelling updated date.
     *
     * @return Carbon
     */
    public function getUpdatedAt()
    {
        return $this->bookingSystemUpdatedAt->gt($this->websiteUpdatedAt) ?
            $this->bookingSystemUpdatedAt :
            $this->websiteUpdatedAt;
    }

}
