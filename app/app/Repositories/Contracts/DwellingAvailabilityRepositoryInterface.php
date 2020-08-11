<?php

namespace App\Repositories\Contracts;

use App\Availability\AvailabilityCollection;

interface DwellingAvailabilityRepositoryInterface
{

    /**
     * Find the booking availability for the dwelling, and its related dwellings if there are any.
     *
     * Takes into consideration whether the dwelling is in a bookable state, any stop sale dates, and any existing
     * bookings.
     *
     * @param int $id Dwelling primary key.
     * @param int $duration Duration in days.
     * @return AvailabilityCollection
     */
    public function findForDwelling($id, $duration = 365);

}
