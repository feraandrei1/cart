<?php

namespace Niladam\Cart\Observers;

use Niladam\Cart\Models\Cart;

class CartObserver
{
    /**
     * Remove the cart items on cart deletion
     *
     * @param  Niladam\Cart\Models\Cart $cart
     * @return void
     */
    public function deleting(Cart $cart)
    {
        $cart->items()->delete();
    }
}
