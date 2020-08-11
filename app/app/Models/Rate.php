<?php


namespace App\Models;


use App\Traits\ModelGetTableNameTrait;
use Illuminate\Database\Eloquent\Model;
use Money\Money;
use Money\Currency;

/**
 * @property Money price
 * @property string on_sale_from
 * @property string on_sale_to
 * @property int base_capacity
 * @property array changeover_days
 * @property bool sb_allowed
 * @property bool min_two_week_stay
 * @property int min_sb_duration
 * @property int weekly_blocks_only
 * @property Money sb_additional_price
 * @property Money price_per_extra_person
 * @property Money sb_price
 */
class Rate extends Model
{

    use ModelGetTableNameTrait;

    const MIN_SB_DURATION_DEFAULT = 7;
    const MIN_TWO_WEEK_STAY = 14;

    protected $table = 'dwelling_price_rows';

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function Dwelling()
    {
        return $this->hasOne(
            Dwelling::class,
            'id',
            'dwelling_id');
    }

    /**
     * @param $price
     * @return Money
     */
    public function getPriceAttribute($price)
    {
        return new Money((int) $price, (new Currency($this->getBaseCurrencyCode())));
    }

    /**
     * @param $price
     * @return Money
     */
    public function getPricePerExtraPersonAttribute($price)
    {
        return new Money((int) $price, (new Currency($this->getBaseCurrencyCode())));
    }

    /**
     * @param $price
     * @return Money
     */
    public function getSbPriceAttribute($price)
    {
        return new Money((int) $price, (new Currency($this->getBaseCurrencyCode())));

    }

    /**
     * @param $price
     * @return Money
     */
    public function getSbAdditionalPriceAttribute($price)
    {
        return new Money((int) $price, (new Currency($this->getBaseCurrencyCode())));
    }

    /**
     * @param $allowed
     * @return bool
     */
    public function getSbAllowedAttribute($allowed)
    {
        return (bool) $allowed;
    }

    /**
     * @return int
     */
    public function getMinimumStayLength()
    {
        if ($this->min_two_week_stay) {
            return self::MIN_TWO_WEEK_STAY;
        }

        if ($this->min_sb_duration) {
            return $this->min_sb_duration;
        }

        return self::MIN_SB_DURATION_DEFAULT;
    }

    /**
     * @return string Currency code
     */
    private function getBaseCurrencyCode()
    {
        return $this->Dwelling->Currency->code;
    }

    public function getChangeoverDaysAttribute($days)
    {
        $map = array(
            'MON' => 'Monday',
            'TUE' => 'Tuesday',
            'WED' => 'Wednesday',
            'THU' => 'Thursday',
            'FRI' => 'Friday',
            'SAT' => 'Saturday',
            'SUN' => 'Sunday',
        );

        return str_ireplace(array_keys($map), $map, $days);
    }

    /**
     * @param $weekly_blocks_only
     * @return bool
     */
    public function getWeeklyBlocksOnlyAttribute($weekly_blocks_only)
    {
        return (bool) $weekly_blocks_only;
    }

}
