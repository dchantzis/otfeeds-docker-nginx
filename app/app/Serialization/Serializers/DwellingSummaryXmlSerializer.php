<?php

namespace App\Serialization\Serializers;

use App\Models\DwellingSummary;
use App\Serialization\Transformers\DwellingSummaryTransformer;
use League\Fractal\Resource\ResourceInterface;

class DwellingSummaryXmlSerializer implements SerializerInterface
{

    /**
     * {@inheritdoc}
     */
    public function serialize(ResourceInterface $data)
    {
        $transformer = new DwellingSummaryTransformer();

        $xml = new \SimpleXMLElement('<DwellingSummary></DwellingSummary>');

        /** @var DwellingSummary $dwelling */
        foreach ($data->getData() as $dwelling) {
            $data = $transformer->transform($dwelling);

            $child = $xml->addChild('Dwelling');
            $child->addChild('DwellingId', $data['id']);
            $child->addChild('LastUpdate', $data['last_update']);
        }

        return $xml;
    }

}
