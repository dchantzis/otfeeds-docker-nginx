<?php

namespace App\Repositories\Contracts;

use App\Services\CurrencyConverter\ExchangeRate;

interface ExchangeRateRepositoryInterface
{
    /**
     * Returns a list of exchange rates.
     *
     * @return ExchangeRate[]
     */
    public function listAll();
}
