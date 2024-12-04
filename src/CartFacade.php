<?php

namespace Niladam\Cart;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Niladam\Cart\Skeleton\SkeletonClass
 */
class CartFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
