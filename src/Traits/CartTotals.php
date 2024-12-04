<?php

namespace Niladam\Cart\Traits;

use App\Support\ShippingCalculator;
use App\User;

trait CartTotals
{
    public function updateTotals()
    {
        $this
            ->setSubtotals()
            ->applyDiscounts()
            ->setShippingCharges()
            ->setTaxes()
            ->setTotal()
            ->setPayableAndRoundOff()
            ->save();

        return $this;
    }

    public function determineFragility(): bool
    {
        return $this->items->contains(fn ($cartItem) => $cartItem->product?->fragile);
    }

    public function setSubtotals(): static
    {
        $this->subtotal = round($this->items->sum(fn ($cartItem) => $cartItem->price * $cartItem->quantity), 2);

        $this->subtotal_with_tax = round($this->items->sum(fn ($cartItem) => $cartItem->price_with_tax * $cartItem->quantity), 2);

        return $this;
    }

    public function applyDiscounts(): static
    {
        if (! $this->auth_user) {
            return $this;
        }

        $discount = User::where('id', $this->auth_user)->first()->discount;

        $this->discount_percentage = $discount ?? null;

        $this->discount = $discount
            ? round(($this->subtotal * $this->discount_percentage) / 100, 2)
            : null;

        $this->discount_with_tax = $discount
            ? round(($this->subtotal * $this->discount_percentage) / 100 * 1.19, 2)
            : null;

        return $this;
    }

    public function setShippingCharges(): static
    {
        if (session('cityId') === 6383) {
            $this->shipping_charges = 0;

            return $this;
        }

        $orderAmount = $this->subtotal;

        $this->shipping_charges = (new ShippingCalculator($orderAmount, $this->total_weight, $this->determineFragility()))->getCosts();

        return $this;
    }

    public function setTaxes(): static
    {
        $this->net_total = round($this->subtotal - $this->discount, 2);

        $this->tax = round(($this->subtotal * config('cart-config.tax_percentage')) / 100, 2);

        return $this;
    }

    public function setTotal(): static
    {
        $discount = $this->discount_with_tax - $this->discount;
        $this->total = round($this->net_total + $this->tax + $this->shipping_charges - $discount, 2);

        return $this;
    }

    public function setPayableAndRoundOff(): static
    {
        $this->payable = match (config('cart-config.round_off_to')) {
            0.05 => round($this->total * 2, 1) / 2,
            0.1 => round($this->total, 1),
            0.5 => round($this->total * 2) / 2,
            1 => round($this->total),
            default => round($this->total),
        };

        $this->round_off = round($this->payable - $this->total, 2);

        return $this;
    }
}
