<?php

namespace App\Serialization\Transformers;

use App\Availability\AvailabilityPeriod;
use League\Fractal\TransformerAbstract;

class DwellingAvailabilityTransformer extends TransformerAbstract
{

    /**
     * @param AvailabilityPeriod $period
     * @return array
     */
    public function transform(AvailabilityPeriod $period)
    {
        return [
            'start_date' => $period->getStartDate()->format('Y-m-d'),
            'end_date' => $period->getEndDate()->format('Y-m-d'),
            'bookable' => $period->isBookable(),
            'weekly_blocks_only' => $period->isWeeklyBlocksOnly()
        ];
    }

}
