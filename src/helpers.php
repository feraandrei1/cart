<?php

use Feraandrei1\Cart\CartManager;

if (! function_exists('cart')) {
    /**
     * Returns an instance of the Cart class.
     */
    function cart()
    {
        return app(CartManager::class);
    }
}

/**
 * Formats the amount in Romanian format.
 */
if (! function_exists('formatAsTotal')) {
    function formatAsTotal($amount, $precision = 2)
    {
        return number_format($amount, $precision, ',', '.');
    }
}
