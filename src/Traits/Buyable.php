<?php

namespace Feraandrei1\Cart\Traits;

trait Buyable
{
    public function getProductName()
    {
        return $this->name;
    }

    public function getProductPrice()
    {
        return $this->price;
    }

    public function getProductPriceWithTax()
    {
        return round(($this->getProductPrice() + $this->getProductPriceTax()), 2);
    }

    public function getProductPriceTax()
    {
        return round(($this->getProductPrice() * config('cart-config.tax_percentage')) / 100, 2);
    }
}
