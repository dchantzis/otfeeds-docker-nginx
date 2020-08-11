<?php

namespace App\Services\CurrencyConverter;

class ExchangeRate
{

    /**
     * @var string Currency code.
     */
    private $from;

    /**
     * @var string Currency code.
     */
    private $to;

    /**
     * @var float Exchange rate.
     */
    private $rate;

    /**
     * @param string $from Currency code.
     * @param string $to Currency code.
     * @param float $rate Exchange rate.
     */
    public function __construct($from, $to, $rate)
    {
        $this->from = strtoupper($from);
        $this->to = strtoupper($to);
        $this->rate = $rate;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

}
