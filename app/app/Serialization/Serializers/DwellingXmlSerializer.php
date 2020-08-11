<?php

namespace App\Serialization\Serializers;


use App\Models\Dwelling;
use App\Serialization\Transformers\DwellingTransformer;
use League\Fractal\Resource\ResourceInterface;

class DwellingXmlSerializer implements SerializerInterface
{

    /**
     * @var DwellingTransformer
     */
    private $transformer;

    /**
     * @param DwellingTransformer $transformer
     */
    public function __construct(DwellingTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(ResourceInterface $data)
    {
        /** @var Dwelling $dwelling */
        $dwelling = $data->getData();
        $data = $this->transformer->transform($dwelling);
        $data = array_pop($data);
        $root = new \SimpleXMLElement('<Dwelling></Dwelling>');

        $root->addChild('DwellingId', $data['id']);
        $root->addChild('LastUpdate', $data['last_update']);

        $address = $root->addChild('Address');
        $this->serializeGeneric($address, $data['address']);

        $details = $root->addChild('Details');
        $this->serializeGeneric($details, $data['details']);

        $urls = $root->addChild('URLs');
        $this->serializeWithMapping($urls, $data['urls'], 'URL', array(
            'Type' => 'type',
            'URL' => 'url',
        ));

        $descriptions = $root->addChild('Descriptions');
        $this->serializeDescriptions($descriptions, $data['descriptions']);

        $extras = $root->addChild('Extras');
        $this->serializeWithMapping($extras, $data['extras'], 'Extra', array('ExtraName' => null));

        $amenities = $root->addChild('Amenities');
        $this->serializeWithMapping($amenities, $data['amenities'], 'Amenity', array('AmenityName' => null));

        $photos = $root->addChild('Photos');
        $this->serializePhotos($photos, $data['photos']);

        $videos = $root->addChild('Videos');
        $this->serializeVideos($videos, $data['videos']);

        $rates = $root->addChild('Rates');
        $this->serializeRates($rates, $data['rates']);

        return $root;
    }

    private function serializeGeneric(\SimpleXMLElement $child, array $data)
    {
        foreach ($data as $key => $value) {
            $name = $this->jsonKeyToXmlName($key);
            $child->$name = $value;
        }
    }

    private function serializeDescriptions(\SimpleXMLElement $child, array $data)
    {
        foreach ($data as $key => $value) {
            if($key == 'terms_and_conditions'){
                $wrapper = $child->addChild('TermsAndConditions');
                foreach($value as $v){
                    $condition = $wrapper->addChild('Term');
                    $condition->title = $v['title'];
                    $condition->details = $v['details'];
                }
            } else {
                $name = $this->jsonKeyToXmlName($key);
                $child->$name = $value;
            }
        }
    }

    private function serializeWithMapping(\SimpleXMLElement $child, array $data, $wrapperElementName, $mapping = array())
    {
        foreach ($data as $value) {
            $wrapper = $child->addChild($wrapperElementName);

            foreach ($mapping as $name => $key) {
                if (is_array($value)) {
                    $wrapper->$name = $value[$key];
                } else {
                    $wrapper->$name = $value;
                }
            }
        }
    }

    private function jsonKeyToXmlName($key)
    {
        $segments = array_map(function ($segment) {
            return ucfirst($segment);
        }, explode('_', $key));

        return implode('', $segments);
    }

    private function serializePhotos(\SimpleXMLElement $child, array $photos)
    {
        foreach ($photos as $photo) {
            $wrapper = $child->addChild('Photo');
            $wrapper->addAttribute('order', $photo['order']);
            $wrapper->URL = $photo['url'];
        }
    }

    private function serializeVideos(\SimpleXMLElement $child, array $videos)
    {
        foreach ($videos as $video) {
            $wrapper = $child->addChild('Video');
            $wrapper->addAttribute('type', $video['type']);
            $wrapper->URL = $video['url'];
        }
    }

    private function serializeRates(\SimpleXMLElement $child, array $rates)
    {
        foreach ($rates as $rate) {
            $wrapper = $child->addChild('Rate');

            foreach ($rate as $key => $value) {
                $name = $this->jsonKeyToXmlName($key);
                if (! is_array($value)) {
                    $wrapper->{$name} = $value;
                } else {
                    $priceElement = $wrapper->addChild($name);
                    foreach ($value as $price) {
                        $priceElement->{$price['currency']} = $price['price'];
                    }
                }
            }
        }
    }

}
