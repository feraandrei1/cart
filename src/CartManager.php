<?php

namespace Feraandrei1\Cart;

use App\Models\Product;
use App\Models\ProductPackage;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Feraandrei1\Cart\Models\Cart;
use Feraandrei1\Cart\Models\CartItem;

use function collect;

class CartManager
{
    protected ?string $cartCookie = null;

    protected ?Cart $cart = null;

    protected ?User $user = null;

    protected string $cookieName;

    public function __construct()
    {
        $this->cookieName = config('cart-config.cookie_name');
        $this->cartCookie = $this->getCartCookie();
        $this->user = Auth::user();
    }

    public function getCartCookieFromSession(): ?string
    {
        return session($this->cookieName);
    }

    public function setCartIdToSession(): void
    {
        $cookie = $this->cartCookie ?? $this->setCartCookie();
        logger('Setting cart cookie session to '.$cookie);
        session()->put($this->cookieName, $cookie);
    }

    public function removeCartCookie(): void
    {
        Cookie::queue(Cookie::forget($this->cookieName));
    }

    public function get(): ?Cart
    {
        $this->cart = $this->getCartByCookieOrUserId();

        if ($this->user) {
            $this->assignToUser();
            $this->deletePreviousCarts();
        }

        if ($this->cart) {
            $this->setCartIdToSession();
        }

        return $this->cart;
    }

    protected function getCartByCookieOrUserId(): ?Cart
    {
        if (! $this->cartCookie && ! $this->user) {
            return null;
        }

        $cart = $this->cartCookie
            ? Cart::whereCookie($this->cartCookie)->with('items')->latest()->first()
            : Cart::whereAuthUser($this->user->id)->with('items')->latest()->first();

        if (! $cart && $this->user) {
            $cart = Cart::whereAuthUser($this->user->id)->with('items')->latest()->first();
        }

        if (! $cart) {
            return null;
        }

        if ($this->cartCookie && $cart->cookie !== $this->cartCookie) {
            logger('Updating the cart cookie from '.$cart->cookie.' to '.$this->cartCookie);
            $cart->update(['cookie' => $this->cartCookie]);
        }

        if ($this->user && $cart->auth_user !== $this->user->id) {
            logger('Updating the cart user from '.$cart->auth_user.' to '.$this->user->id);
            $cart->update(['auth_user' => $this->user->id]);
        }

        return $cart;
    }

    public function cleanUp(): void
    {
        session()->forget(['cartId', 'cartCookie', $this->cookieName]);
        $this->removeCartCookie();
    }

    public function deletePreviousCarts(): void
    {
        if ($this->cart && $this->cart->id) {
            $previousCarts = Cart::whereAuthUser($this->user->id)->where('id', '!=', $this->cart->id)->with('items')->get();

            if (! $previousCarts) {
                return;
            }

            foreach ($previousCarts as $previousCart) {
                $this->mergeCarts($previousCart, $this->cart);
            }

            $this->cart->updateTotals();
        }
    }

    public function getOrCreateCart(): ?Cart
    {
        if (! empty($this->cart)) {
            return $this->cart;
        }

        $this->cart = Cart::firstOrCreate(
            ['cookie' => $this->cartCookie ?? $this->setCartCookie()],
            ['auth_user' => optional($this->user)->id]
        );

        $this->setCartIdToSession();

        return $this->cart;
    }

    public function getCartCookie(): ?string
    {
        return request()?->hasCookie($this->cookieName)
            ? Cookie::get($this->cookieName)
            : $this->getCartCookieFromSession();
    }

    public function setCartCookie(): string
    {
        $callee = debug_backtrace()[1]['function'];

        $this->cartCookie = Str::random(20);

        logger(sprintf('Setting cart cookie to %s from %s', $this->cartCookie, $callee));

        Cookie::queue(cookie()->forever(name: $this->cookieName, value: $this->cartCookie));

        return $this->cartCookie;
    }

    public function addMany(array $products, $source = null): ?Cart
    {
        $this->getOrCreateCart();

        $newProducts = collect($products)->transform(function ($item) use ($source) {
            $quantity = $item['quantity'];
            $product = $item['product'];

            return CartItem::updateOrCreate(
                [
                    'cart_id' => $this->cart->id,
                    'model_type' => $product->getMorphClass(),
                    'model_id' => $product->id,
                ],
                [
                    'name' => $product->getProductName(),
                    'price' => $product->getProductPrice(),
                    'price_with_tax' => $product->getProductPriceWithTax(),
                    'tax' => $product->getProductPriceTax(),
                    'source' => $source,
                    'quantity' => $quantity,
                ]);
        });

        $this->cart->items()->saveMany($newProducts->all());
        $this->cart->refresh();
        $this->cart->save();
        $this->cart->updateTotals();

        return $this->cart;
    }

    public function addProductPackage(ProductPackage $productPackage, $source = null): ?Cart
    {
        $this->getOrCreateCart();

        $newProducts = collect($productPackage->products)
            ->transform(fn (Product $product) => new CartItem([
                'cart_id' => $this->cart->id,
                'model_type' => $product->getMorphClass(),
                'model_id' => $product->id,
                'name' => $product->getProductName(),
                'price' => $product->getProductPrice(),
                'price_with_tax' => $product->getProductPriceWithTax(),
                'tax' => $product->getProductPriceTax(),
                'source' => $source,
                'quantity' => 1
            ])
        );

        $this->cart->items()->saveMany($newProducts->all());
        $this->cart->refresh();
        $this->cart->save();
        $this->cart->updateTotals();

        return $this->cart;
    }

    protected function mergeCarts(Cart $oldCart, Cart $cart): void
    {
        foreach ($oldCart->items as $item) {
            $cartItem = CartItem::updateOrCreate(
                [
                    'cart_id' => $this->cart->id,
                    'model_type' => $item->model_type,
                    'model_id' => $item->model_id,
                ],
                [
                    'name' => $item->name,
                    'price' => $item->price,
                    'price_with_tax' => $item->price_with_tax,
                    'tax' => $item->tax,
                    'source' => $item->source,
                ]
            );

            $cartItem->refresh();

            $cartItem->update(['quantity' => $cartItem->quantity + $item->quantity]);

            $this->cart->items()->save($cartItem);
        }

        $oldCart->delete();
    }

    public function add(Product $product, $quantity = 1, $source = null): ?Cart
    {
        $this->getOrCreateCart();

        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $this->cart->id,
                'model_type' => $product->getMorphClass(),
                'model_id' => $product->id,
            ],
            [
                'name' => $product->getProductName(),
                'price' => $product->getProductPrice(),
                'price_with_tax' => $product->getProductPriceWithTax(),
                'tax' => $product->getProductPriceTax(),
                'source' => $source,
            ],
        );

        $cartItem->refresh();

        $cartItem->update(['quantity' => $cartItem->quantity + $quantity]);

        $this->cart->items()->save($cartItem);

        $this->cart->refresh();

        $this->cart->save();

        $this->cart->updateTotals();

        return $this->cart;
    }

    public function removeCookieIfLastItem(): void
    {
        if (count($this->cart->items) === 1) {
            $this->removeCartCookie();
        }
    }

    public function remove($cartItemId): ?Cart
    {
        $this->get();

        if ($this->cartHas($cartItemId) && count($this->cart->items) === 1) {
            $this->removeCookieIfLastItem();
            $this->cart->delete();
            $this->cleanUp();

            return null;
        }

        CartItem::where(['id' => $cartItemId])->delete();

        $this->cart->refresh();

        $this->cart->save();

        $this->cart->updateTotals();

        return $this->cart;
    }

    public function updateQuantity(Product $product, $quantity): ?Cart
    {
        $this->getOrCreateCart();

        $this->cart->items()->where([
            'cart_id' => $this->cart->id,
            'model_type' => $product->getMorphClass(),
            'model_id' => $product->id,
        ])->update(['quantity' => $quantity]);

        $this->cart->refresh();

        $this->cart->save();

        $this->cart->updateTotals();

        return $this->cart;
    }

    protected function cartHas($cartItemId): bool
    {
        if ($this->cart) {
            return $this->cart->items->contains(fn ($item) => $item->id === $cartItemId);
        }

        return false;
    }

    protected function assignToUser(): void
    {
        if (! $this->cart) {
            return;
        }

        if ($this->cart->auth_user) {
            return;
        }

        if (! $this->user) {
            return;
        }

        if ($this->user->id === $this->cart->auth_user) {
            return;
        }

        $this->cart->update([
            'auth_user' => $this->user->id,
        ]);
    }
}
