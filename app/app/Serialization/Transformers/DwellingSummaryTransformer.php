<?php

namespace App\Serialization\Transformers;

use App\Models\DwellingSummary;
use League\Fractal\TransformerAbstract;

class DwellingSummaryTransformer extends TransformerAbstract
{

    /**
     * Transform a DwellingSummary into an array format for later serialization.
     *
     * @param DwellingSummary $dwelling
     * @return array
     */
    public function transform(DwellingSummary $dwelling)
    {
        $a = 'a';

        return [
            'id' => $dwelling->getId(),
            'last_update' => $dwelling->getUpdatedAt()->toDateTimeString(),
        ];
    }

}
