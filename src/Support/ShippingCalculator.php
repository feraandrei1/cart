<?php

namespace Feraandrei1\Cart\Support;

class ShippingCalculator
{
    protected float $shippingDefaultCost = 17;

    protected float $percentageAmount = 0.02;

    protected float $bulkPricePerKg = 1.20;

    protected float $pricePerKg = 1.20;

    protected int $fragileCost = 5;

    public function __construct(
        protected float $amount,
        protected float $weight,
        protected bool $isFragile = false,
    ) {
    }

    public function getCosts(): float
    {
        $cost = $this->isOverWeight() ? $this->getOverWeightCost() : $this->getRegularCost();

        $cost += $this->isFragile ? $this->fragileCost : 0;

        return $this->getFormattedCost($cost);
    }

    protected function isOverWeight(): bool
    {
        return $this->weight >= 150;
    }

    protected function getOverWeightCost(): float
    {
        return ($this->weight * $this->bulkPricePerKg) - ($this->amount * $this->percentageAmount);
    }

    protected function getRegularCost(): float
    {
        return ($this->weight * $this->pricePerKg) - ($this->amount * $this->percentageAmount) + $this->shippingDefaultCost;

        return $this->weight - ($this->amount * $this->percentageAmount) + $this->shippingDefaultCost;
    }

    protected function getFormattedCost(float $cost): float
    {
        $cost = max($cost, 0);

        return number_format(num: floor($cost * 100) / 100, decimals: 2, thousands_separator: '');
    }
}
