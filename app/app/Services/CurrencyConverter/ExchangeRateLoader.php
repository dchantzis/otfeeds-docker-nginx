<?php

namespace App\Services\CurrencyConverter;

use App\Repositories\Contracts\ExchangeRateRepositoryInterface;
use App\Services\CurrencyConverter;

/**
 * Loads the exchange rate information from the database and populates a @see CurrencyConverter with
 * the exchange rates.
 */
class ExchangeRateLoader
{

    /**
     * @var ExchangeRateRepositoryInterface
     */
    private $rates;

    /**
     * @param ExchangeRateRepositoryInterface $rates
     */
    public function __construct(ExchangeRateRepositoryInterface $rates)
    {
        $this->rates = $rates;
    }

    /**
     * @param CurrencyConverter $converter
     * @return CurrencyConverter
     */
    public function load(CurrencyConverter $converter)
    {
        foreach ($this->rates->listAll() as $rate) {
            $converter->addExchangeRate($rate);
        }

        return $converter;
    }

}
