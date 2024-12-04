<?php

namespace Feraandrei1\Cart;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Feraandrei1\Cart\Skeleton\SkeletonClass
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
