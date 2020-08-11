<?php

namespace App\Services;

use App\Services\CurrencyConverter\Exceptions\NoExchangeRateFoundException;
use App\Services\CurrencyConverter\ExchangeRate;
use Money\Money;

class CurrencyConverter
{

    /**
     * @var ExchangeRate[]
     */
    private $rates = array();

    /**
     * Add an exchange rate that can be used when exchanging from one currency to another.
     *
     * @param ExchangeRate $rate
     */
    public function addExchangeRate(ExchangeRate $rate)
    {
        $this->rates[] = $rate;
    }

    /**
     * Exchange from one currency to another.
     *
     * @param Money $amount The amount to exchange from.
     * @param string $to Three character currency code to exchange to.
     *
     * @return Money
     */
    public function exchange(Money $amount, $to)
    {
        $from = strtoupper($amount->getCurrency()->getCode());
        $to = strtoupper($to);

        // Converting to the same exchange rate - nothing to do.
        if ($from === $to) {
            return $amount;
        }

        foreach ($this->rates as $rate) {
            if ($from === $rate->getFrom() && $to === $rate->getTo()) {
                return $amount->multiply($rate->getRate());
            }
        }

        throw new NoExchangeRateFoundException(sprintf('There is no exchange rate to exchange %s to %s.', $from, $to));
    }

}
