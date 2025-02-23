<?php

namespace Feraandrei1\Cart\Support;

use Feraandrei1\Cart\Models\Cart;
use App\Models\Product;

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

    protected function hasOversizedProducts(): bool
    {
        return $this->cart->items()
            ->where('model_type', Product::class)
            ->whereHas('product', fn($query) => $query->where('oversized', true))
            ->exists();
    }

    protected function getRegularCost(): float
    {
        return ($this->weight * $this->pricePerKg) - ($this->amount * $this->percentageAmount) + $this->shippingDefaultCost;
    }

    protected function getOversizedCost(): float
    {
        $regularCost = $this->getRegularCost();

        $totalOversizedShipping = $this->cart->items()
            ->where('model_type', Product::class)
            ->whereHas('model', fn($query) => $query->where('oversized', true))
            ->get()
            ->sum(fn($item) => $item->quantity * $item->product->oversized_shipping_price);

        return $regularCost + $totalOversizedShipping;
    }

    protected function getFormattedCost(float $cost): float
    {
        $cost = max($cost, 0);

        return number_format(num: floor($cost * 100) / 100, decimals: 2, thousands_separator: '');
    }
}
