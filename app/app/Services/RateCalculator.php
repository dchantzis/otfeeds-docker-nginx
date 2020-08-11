<?php

namespace App\Services;

use Money\Money;

class RateCalculator
{

    /**
     * @param Money $price
     * @param $commissionMarkup
     * @param $bookingFeeRate
     * @param $salesTaxRate
     * @param null $salesTaxGroup
     * @return Money
     */
    public function calculate(Money $price, $commissionMarkup, $bookingFeeRate, $salesTaxRate, $salesTaxGroup = null)
    {
        return $price
            ->add($this->calculateAdminFee($price, $commissionMarkup))
            ->add($this->calculateBookingFee($price, $commissionMarkup, $bookingFeeRate))
            ->add($this->calculateSalesTax($price, $salesTaxRate, $salesTaxGroup));
    }

    /**
     * @param Money $price
     * @param float $commissionMarkup
     * @return Money
     */
    private function calculateAdminFee($price, $commissionMarkup)
    {
        return $price
            ->multiply(1 / (1 - $commissionMarkup))
            ->subtract($price);
    }

    /**
     * @param $price
     * @param $commissionMarkup
     * @param $bookingFeeRate
     * @return mixed
     */
    private function calculateBookingFee($price, $commissionMarkup, $bookingFeeRate)
    {
        return $price
            ->add($this->calculateAdminFee($price, $commissionMarkup))
            ->multiply($bookingFeeRate);
    }

    /**
     * @param $price
     * @param $salesTaxRate
     * @param $salesTaxGroup
     * @return mixed
     */
    private function calculateSalesTax($price, $salesTaxRate, $salesTaxGroup)
    {
        if ($salesTaxGroup === null || $salesTaxGroup === 'SALES') {
            return $price->multiply($salesTaxRate);
        }
        return $price->multiply(0);
    }

}
