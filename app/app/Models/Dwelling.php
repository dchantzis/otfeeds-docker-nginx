<?php

namespace App\Models;

use App\Models\AbstractModel;
use App\Models\Simply\AdditionalInformation;
use App\Traits\ModelGetTableNameTrait;
use Carbon\Carbon;

/**
 * @property int id
 * @property string address_one
 * @property string address_two
 * @property string town
 * @property string county
 * @property string postcode
 * @property string country
 * @property string name
 * @property int max_capacity
 * @property int base_capacity
 * @property int num_bedrooms
 * @property int num_bathrooms
 * @property int licence_number
 * @property float commission_markup
 * @property float booking_fee_rate
 * @property int sale_state
 * @property Carbon updated_at
 * @property int avantio_sync
 * @property int avantio_accommodation_code
 * @property int avantio_user_code
 *
 * @property AdditionalInformation AdditionalInformation
 * @property Currency Currency
 * @property Rate[] Rates
 * @property TaxBand TaxBand
 *
 * {@inheritdoc}
 */
class Dwelling extends AbstractModel
{

    use ModelGetTableNameTrait;

    protected $table = 'dwellings';

    const SALE_STATE_ON_SALE = 1;
    const SALE_STATE_REVOKED = 2;

    public function AdditionalInformation()
    {
        return $this->hasOne(
            AdditionalInformation::class,
            'booking_system_id'
        );
    }

    public function Currency()
    {
        return $this->hasOne(
            Currency::class,
            'id',
            'currency_id');
    }

    public function Rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function TaxBand()
    {
        return $this->hasOne(
            TaxBand::class,
            'id',
            'tax_band_id'
        );
    }

    public function scopeSaleable($query)
    {
        return $query
            ->where('sale_state', self::SALE_STATE_ON_SALE)
            ->whereHas('AdditionalInformation', function ($q) {
                $q->where('hidden', 0)
                    ->where('deleted', 0);
            });
    }

    protected function generateTableName()
    {
        return $this->table;
    }

    /**
     * Returns the later of the updated_at property in this class or tstamp from {@see AdditionalInformation}.
     */
    public function getUpdatedAt()
    {
        if (! isset($this->AdditionalInformation)) {
            return $this->updated_at;
        }

        $additionalInformationLastChanged = Carbon::createFromTimestampUTC($this->AdditionalInformation->tstamp);

        if ($additionalInformationLastChanged->gt($this->updated_at)) {
            return $additionalInformationLastChanged;
        }

        return $this->updated_at;
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

}
