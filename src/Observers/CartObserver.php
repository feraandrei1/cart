<?php

namespace Feraandrei1\Cart\Observers;

use Feraandrei1\Cart\Models\Cart;

class CartObserver
{
    /**
     * Remove the cart items on cart deletion
     *
     * @param  Feraandrei1\Cart\Models\Cart $cart
     * @return void
     */
    public function deleting(Cart $cart)
    {
        $cart->items()->delete();
    }
}
