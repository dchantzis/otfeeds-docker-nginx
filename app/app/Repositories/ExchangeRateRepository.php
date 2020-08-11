<?php


namespace App\Repositories;

use App\Models\ExchangeRate as ExchangeRateModel;
use App\Repositories\Contracts\ExchangeRateRepositoryInterface;
use App\Services\CurrencyConverter\ExchangeRate as ExchangeRateValueObject;

class ExchangeRateRepository implements ExchangeRateRepositoryInterface
{

    /**
     * @var ExchangeRateModel
     */
    private $model;

    public function __construct(ExchangeRateModel $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function listAll()
    {
        $rates = array();

        /** @var ExchangeRateModel $rate */
        foreach ($this->model->with('FromCurrency', 'ToCurrency')->get() as $rate) {
            $rates[] = new ExchangeRateValueObject($rate->FromCurrency->code, $rate->ToCurrency->code, $rate->rate);
        }

        return $rates;
    }

}
