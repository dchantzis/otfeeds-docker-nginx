<?php

namespace App\Services;

use Illuminate\Config\Repository as Config;

class GeoCoder
{

    /**
     * Laravel config repo.
     *
     * @var Config
     */
    private $config;

    /**
     * Google Maps API key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * GeoCoder constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
        $this->apiKey = $this->config->get('services.google.maps.key');
    }

    /**
     * Reverse geocode dwelling location to get correctly formatted city name
     *
     * @param string            $lat location latitude
     * @param string            $lng location longitude
     * @param string            $format json/xml
     * @return string/false
     */
    public function getCityReverse($lat, $lng, $format = 'json')
    {
        if ($lat != '' && $lng != '') {

            $request = sprintf(
              '%s?latlng=%s,%s',
                'https://maps.google.com/maps/api/geocode/',
                trim($lat),
                trim($lng)
            );

            $geo = json_decode(file_get_contents(!empty($this->apiKey) ? $request.'&key='.$this->apiKey : $request));

            if ($geo && $geo->results) {
                foreach ($geo->results as $key => $value) {
                    if (in_array('locality', $value->address_components[0]->types)) {
                        return $value->address_components[0]->long_name;
                    }
                    else if (in_array('postal_town', $value->address_components[0]->types)) {
                        return $value->address_components[0]->long_name;
                    }
                }
            }
        }
        return false;
    }

}
