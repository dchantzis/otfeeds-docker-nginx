<?php

namespace App\Models;

use App\Traits\ModelGetTableNameTrait;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{

    use ModelGetTableNameTrait;

    protected $table = 'exchange_rates';

    public function FromCurrency()
    {
        return $this->hasOne(
            Currency::class,
            'id',
            'base_currency_id'
        );
    }

    public function ToCurrency()
    {
        return $this->hasOne(
            Currency::class,
            'id',
            'compare_currency_id'
        );
    }

}
