<?php

namespace App\Models\Simply;

use App\Models\AbstractModel;
use App\Models\Simply\Dwelling as SimplyDwelling;
use App\Models\Dwelling;

/**
 * Additional dwelling information.
 *
 * @property string lat
 * @property string lng
 * @property string overview
 * @property string rental_rates
 * @property string location_info
 * @property string capacity_info
 * @property string catering_services
 * @property string wedding_conference_info
 * @property string special_offers
 * @property string interior_grounds
 * @property string getting_there
 * @property string terms_conditions
 * @property int tstamp
 * @property string youtube_url
 * @property int min_sb_duration
 * @property string[] images
 * @property int booking_system_id
 *
 * @property DwellingType DwellingType
 * @property DwellingStyle DwellingStyle
 * @property Extra[] Extras
 * @property Facility[] Facilities
 * @property Region Region
 *
 */

class AdditionalInformation extends AbstractModel
{

    protected $primaryKey = 'uid';

    public $timestamps = false;

    public function DwellingType()
    {
        return $this->hasOne(
            DwellingType::class,
            'uid',
            'property_type'
        );
    }

    public function Extras()
    {
        return $this->belongsToMany(
            Extra::class,
            DwellingExtra::getTableName(),
            'uid_local',
            'uid_foreign'
        );
    }

    public function Region()
    {
        return $this->hasOne(
            Region::class,
            'uid',
            'region'
        );
    }

    public function DwellingStyle()
    {
        return $this->hasOne(
            DwellingStyle::class,
            'uid',
            'property_style'
        );
    }

    public function Facilities()
    {
        return $this->belongsToMany(
            Facility::class, //tx_simply_facilities
            DwellingFacility::getTableName(), //tx_simply_dwellings_facility_mm
            'uid_local',
            'uid_foreign'
        );
    }

    public function Terms()
    {
        return $this->belongsToMany(
            Terms::class,
            DwellingTermsAndConditions::getTableName(),
            'tx_simply_dwellings',
            'tx_simply_termsandconditions'
        )->withPivot('details');
    }

    public function getImagesAttribute($raw)
    {
        return array_map(function ($filename) {
            return sprintf(
                'https://static.oliverstravels.com/uploads/tx_oliverstravels/tx_simply_dwellings/%s',
                $filename
            );
        }, str_getcsv($raw));
    }

    public function __get($name)
    {
        if (isset($this->attributes[$name])) {
            $value = $this->attributes[$name];

            if ('' === $value) {
                return;
            }

            if (ctype_digit($value)) {
                return (int) $value;
            }

            return parent::__get($name);
        }

        return parent::__get($name);
    }

    protected function generateTableName()
    {
        return SimplyDwelling::getTableName();
    }

}
