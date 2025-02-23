<?php

namespace Feraandrei1\Cart\Support;

use Feraandrei1\Cart\Models\Cart;

class ShippingCalculator
{
    protected float $shippingDefaultCost = 17;

    protected float $percentageAmount = 0.02;

    protected float $bulkPricePerKg = 1.20;

    protected float $pricePerKg = 1.20;

    protected int $fragileCost = 5;

    public function __construct(
        protected Cart $cart,
        protected float $amount,
        protected float $weight,
        protected bool $isFragile = false,
    ) {}

    public function getCosts(): float
    {
        if (! $this->hasOversizedProducts()) {
            $cost = $this->getRegularCost();
        }

        if ($this->hasOversizedProducts()) {
            $cost = $this->getOversizedCost();
        }

        $cost += $this->isFragile ? $this->fragileCost : 0;

        return $this->getFormattedCost($cost);
    }

    protected function getRegularCost(): float
    {
        return ($this->weight * $this->pricePerKg) - ($this->amount * $this->percentageAmount) + $this->shippingDefaultCost;
    }

    protected function getFormattedCost(float $cost): float
    {
        $cost = max($cost, 0);

        return number_format(num: floor($cost * 100) / 100, decimals: 2, thousands_separator: '');
    }

    protected function getOversizedCost(): float
    {
        return $this->getRegularCost() + $this->getOversizedProducts()
            ->get()
            ->sum(fn($item) => $item->quantity * $item->product->oversized_shipping_price);
    }

    protected function getOversizedProducts()
    {
        return $this->cart->items()->whereHas('product', function ($query) {
            $query->where('oversized', true)->whereNotNull('oversized_shipping_price');
        });
    }

    protected function hasOversizedProducts(): bool
    {
        return $this->getOversizedProducts()->exists();
    }
}
