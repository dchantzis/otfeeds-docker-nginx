<?php

namespace App\Serialization\Transformers;

use App\Models\Consumer;
use App\Models\Dwelling;
use App\Models\Rate;
use App\Services\CurrencyConverter;
use App\Services\RateCalculator;
use App\Services\TrackingCodeAppender;
use App\Services\CurrencyConverter\ExchangeRateLoader;
use App\Services\UrlGenerator;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Auth;

class DwellingTransformer extends TransformerAbstract
{

    /**
     * @var array
     */
    private $data;

    /**
     * @var Dwelling
     */
    private $dwelling;

    /**
     * @var RateCalculator
     */
    private $calculator;

    /**
     * @var CurrencyConverter
     */
    private $converter;

    /**
     * @var TrackingCodeAppender
     */
    private $tracking;

    /**
     * @param RateCalculator $calculator
     * @param ExchangeRateLoader $loader
     * @param Auth $auth
     */
    public function __construct(RateCalculator $calculator, ExchangeRateLoader $loader, Auth $auth)
    {
        $this->calculator = $calculator;
        $this->converter = $loader->load(new CurrencyConverter());

        /** @var Consumer $consumer */
        $consumer = $auth::user();

        /** This conditional statement is used for allowing 3ev internal debugging and health-check endpoints to work */
        if ($consumer instanceof Consumer) {
            $this->tracking = new TrackingCodeAppender($consumer->utm_source, $consumer->utm_medium, $consumer->utm_campaign);
        }
    }

    /**
     * @param Dwelling $dwelling
     * @return array
     */
    public function transform(Dwelling $dwelling)
    {
        $this->dwelling = $dwelling;

        $dwellingType = $dwelling->AdditionalInformation->DwellingType;

        $terms = [];
        if ($dwelling->AdditionalInformation->Terms)
        {
            foreach ($dwelling->AdditionalInformation->Terms as $extra) {
                $terms[] = array(
                    'title' => $extra->title,
                    'details' => $extra->pivot->details
                );
            }
        }

        $this->data = [
            'id' => $dwelling->id,
            'last_update' => $dwelling->getUpdatedAt()->toDateTimeString(),
            'address' => array(
                'address1' => $dwelling->address_one,
                'address2' => $dwelling->address_two,
                'city' => $dwelling->town,
                'state' => $dwelling->county,
                'zip_code' => $dwelling->postcode,
                'country' => $dwelling->country,
                'latitude' => $dwelling->AdditionalInformation->lat,
                'longitude' => $dwelling->AdditionalInformation->lng,
            ),
            'details' => [
                'dwelling_name' => $dwelling->name,
                'dwelling_type' => $dwellingType ? $dwellingType->title : '',
                'maximum_capacity' => $dwelling->max_capacity,
                'base_capacity' => $dwelling->base_capacity,
                'bedrooms' => $dwelling->num_bedrooms,
                'bathrooms' => $dwelling->num_bathrooms,
                'currency' => $dwelling->Currency->code,
                'licence_number' => $dwelling->licence_number,
            ],
            'urls' => [],
            'descriptions' => [
                'dwelling_description' => $dwelling->AdditionalInformation->overview,
                'rate_description' => $dwelling->AdditionalInformation->rental_rates,
                'location_description' => $dwelling->AdditionalInformation->location_info,
                'capacity_info' => $dwelling->AdditionalInformation->capacity_info,
                'catering_services' => $dwelling->AdditionalInformation->catering_services,
                'wedding_conference_info' => $dwelling->AdditionalInformation->wedding_conference_info,
                'special_offers' => $dwelling->AdditionalInformation->special_offers,
                'interior_grounds' => $dwelling->AdditionalInformation->interior_grounds,
                'getting_there' => $dwelling->AdditionalInformation->getting_there,
                'terms_and_conditions' => $terms,
            ],
            'extras' => [],
            'amenities' => [],
            'photos' => [],
            'videos' => [
                [
                    'type' => 'youtube',
                    'url' => $dwelling->AdditionalInformation->youtube_url,
                ],
            ],
            'rates' => [],
        ];

        $this->addUrls();
        $this->addExtras();
        $this->addRates();
        $this->addAmenities();
        $this->addPhotos();

        return [$this->data];
    }

    private function addExtras()
    {
        foreach ($this->dwelling->AdditionalInformation->Extras as $extra) {
            $this->data['extras'][] = $extra->title;
        }
    }

    private function addRates()
    {

        $commissionMarkup = $this->dwelling->commission_markup;
        $bookingFeeRate = $this->dwelling->booking_fee_rate;
        $salesTaxRate = $this->dwelling->TaxBand->rate;
        $salesTaxGroup = $this->dwelling->TaxBand->tax_group;

        /** @var Rate $rate */
        foreach ($this->dwelling->Rates as $rate) {

            $data = array(
                'on_sale_from' => $rate->on_sale_from,
                'on_sale_until' => $rate->on_sale_to,
                'base_capacity' => $rate->base_capacity,
                'changeover_days' => $rate->changeover_days,
                'weekly_price' => array(),
                'minimum_stay_length' => $rate->getMinimumStayLength(),
                'price_per_extra_person_per_night' => array(),
                'short_break_allowed' => $rate->sb_allowed,
                'min_short_break_duration' => $rate->min_sb_duration,
                'short_break_price' => array(),
                'short_break_additional_day_price' => $rate->sb_additional_price,
                'weekly_blocks_only' => $rate->weekly_blocks_only,
            );

            $data['weekly_price'] = $this->calculatePrices($rate->price, $commissionMarkup, $bookingFeeRate, $salesTaxRate, $salesTaxGroup);

            // Only return extra person per night information if extra people are allowed.
            if ($rate->base_capacity === $this->dwelling->max_capacity) {
                unset($data['price_per_extra_person_per_night']);
            } else {
                $data['price_per_extra_person_per_night'] = $this->calculatePrices($rate->price_per_extra_person, $commissionMarkup, $bookingFeeRate, $salesTaxRate, $salesTaxGroup);
            }

            // Only return short break information if short breaks are allowed or if the rate is weekly blocks only.
            if ((! $rate->sb_allowed) || ($rate->weekly_blocks_only)) {
                unset(
                    $data['min_short_break_duration'],
                    $data['short_break_price'],
                    $data['short_break_additional_day_price']
                );
            } else {
                $data['short_break_price'] = $this->calculatePrices($rate->sb_price, $commissionMarkup, $bookingFeeRate, $salesTaxRate, $salesTaxGroup);
                $data['short_break_additional_day_price'] = $this->calculatePrices($rate->sb_additional_price, $commissionMarkup, $bookingFeeRate, $salesTaxRate, $salesTaxGroup);
            }

            $this->data['rates'][] = $data;
        }

        /* If on_sale_until is null, calculate from next rates start date*/
        $hasStoredEndDate = false;
        for($i = 0; $i < count($this->data['rates']); $i++) {
            $rate = $this->data['rates'][$i];

            if ($i == count($this->data['rates']) - 1) {
                $newEndDate = Carbon::parse($rate['on_sale_from'])->addYears(5);
                $this->data['rates'][$i]['on_sale_until'] = $newEndDate->toDateString();
                if($i && strtotime($this->data['rates'][$i]['on_sale_from']) != strtotime($this->data['rates'][$i - 1]['on_sale_from']) && strtotime($this->data['rates'][$i]['on_sale_until']) == strtotime($this->data['rates'][$i - 1]['on_sale_until'])) {
                    $this->data['rates'][$i]['on_sale_from'] = $this->data['rates'][$i - 1]['on_sale_from'];
                }
            } else {
                for($j = $i + 1; $j < count($this->data['rates']); $j++) {
                    $nextRate = null;
                    if($this->data['rates'][$i]['on_sale_from'] != $this->data['rates'][$j]['on_sale_from']) {
                        $nextRate = $this->data['rates'][$j];
                        break;
                    }
                }
                if(!$nextRate) {
                    $nextRate = $this->data['rates'][$i];
                    $nextRateStartDate = Carbon::parse($nextRate['on_sale_from']);
                    $thisRateEndDate = $nextRateStartDate->addYears(5);
                } else {
                    $nextRateStartDate = Carbon::parse($nextRate['on_sale_from']);
                    $thisRateEndDate = $nextRateStartDate->subDay();
                }
                if($hasStoredEndDate && $i && strtotime($rate['on_sale_from']) < strtotime($this->data['rates'][$i - 1]['on_sale_until'])) {
                    $prevRateEndDate = $this->data['rates'][$i - 1]['on_sale_until'];
                    $this->data['rates'][$i]['on_sale_from'] = $prevRateEndDate;
                    $hasStoredEndDate = false;
                }
                if(!$rate['on_sale_until']) {
                    $this->data['rates'][$i]['on_sale_until'] = $thisRateEndDate->toDateString();
                } else {
                    $hasStoredEndDate = true;
                }
                if($i && strtotime($this->data['rates'][$i]['on_sale_from']) != strtotime($this->data['rates'][$i - 1]['on_sale_from']) && strtotime($this->data['rates'][$i]['on_sale_until']) == strtotime($this->data['rates'][$i - 1]['on_sale_until'])) {
                    $this->data['rates'][$i]['on_sale_from'] = $this->data['rates'][$i - 1]['on_sale_from'];
                }
            }
        }
    }

    private function addAmenities()
    {
        foreach ($this->dwelling->AdditionalInformation->Facilities as $facility) {
            $this->data['amenities'][] = $facility->title;
        }
    }

    private function addPhotos()
    {
        if (is_array($this->dwelling->AdditionalInformation->images)) {
            foreach ($this->dwelling->AdditionalInformation->images as $order => $image) {
                $this->data['photos'][] = array(
                    'order' => $order,
                    'url' => $this->tracking->addTracking($image),
                );
            }
        }
    }

    private function calculatePrices($basePrice, $commissionMarkup, $bookingFeeRate, $salesTaxRate, $salesTaxGroup)
    {
        $totalWeeklyPrice = $this->calculator->calculate(
            $basePrice,
            $commissionMarkup,
            $bookingFeeRate,
            $salesTaxRate,
            $salesTaxGroup
        );

        return array(
            array(
                'currency' => 'GBP',
                'price' => (int)$this->converter->exchange($totalWeeklyPrice, 'GBP')->getAmount(),
            ),
            array(
                'currency' => 'USD',
                'price' => (int)$this->converter->exchange($totalWeeklyPrice, 'USD')->getAmount(),
            ),
            array(
                'currency' => 'EUR',
                'price' => (int)$this->converter->exchange($totalWeeklyPrice, 'EUR')->getAmount(),
            ),
        );

    }

    private function addUrls()
    {
        $generator = new UrlGenerator($this->dwelling->id);

        foreach ($generator->generateUrls() as $type => $url) {
            $this->data['urls'][] = [
                'type' => $type,
                'url' => $this->tracking->addTracking($url),
            ];
        }
    }

}
