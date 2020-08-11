<?php

namespace App\Serialization\Serializers;

use App\Availability\AvailabilityPeriod;
use App\Serialization\Serializers\SerializerInterface;
use App\Serialization\Transformers\DwellingAvailabilityTransformer;
use League\Fractal\Resource\ResourceInterface;

class DwellingAvailabilityXmlSerializer implements SerializerInterface
{

    /**
     * {@inheritdoc}
     */
    public function serialize(ResourceInterface $data)
    {
        $transformer = new DwellingAvailabilityTransformer();

        $xml = new \SimpleXMLElement('<Availability></Availability>');

        /** @var AvailabilityPeriod $period */
        foreach ($data->getData() as $period) {
            $data = $transformer->transform($period);

            $child = $xml->addChild('Period');
            $child->addChild('StartDate', $data['start_date']);
            $child->addChild('EndDate', $data['end_date']);
            // Convert the boolean to a string to match the spec.
            $child->addChild('Bookable', $data['bookable'] ? 'true' : 'false');
        }

        return $xml;
    }

}
