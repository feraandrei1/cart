<?php

namespace Feraandrei1\Cart\Models;

use Feraandrei1\Cart\Support\WeightCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Feraandrei1\Cart\Traits\CartTotals;

class Cart extends Model
{
    use CartTotals;

    protected $appends = ['items_count', 'total_weight', 'is_fragile'];

    protected $with = ['items'];

    /**
     * Guarded attributes
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the items of the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Retrieve the Items Count attribute.
     *
     * @return string
     */
    public function getItemsCountAttribute()
    {
        return $this->items->isNotEmpty() ? $this->items->sum('quantity') : null;
    }

    /**
     * Retrieve the Shipping Tax attribute.
     *
     *
     * @return string
     */
    public function getShippingTaxAttribute()
    {
        return $this->shipping_charges * 0.19;
    }

    public function getIsFragileAttribute(): bool
    {
        return $this->items->contains(fn ($cartItem) => $cartItem->product?->fragile);
    }

    /**
     * Retrieve the Shipping Without Tax attribute.
     *
     *
     * @return string
     */
    public function getShippingWithoutTaxAttribute()
    {
        return $this->shipping_charges - $this->shipping_tax;
    }

    public function summary()
    {
        return $this->only([
            'subtotal',
            'tax',
            'total',
            'total_display',
            'subtotal_with_tax',
            'shipping_charges',
            'itemsList',
            'items_count',
            'discount',
            'discount_percentage',
            'discount_with_tax',
            'total_weight',
            'is_fragile',
        ]);
    }

    /**
     * Retrieve the ItemsList attribute.
     *
     * @return array
     */
    public function getItemsListAttribute(): array
    {
        return $this->items?->toArray();
    }

    /**
     * Get the cart's total weight.
     *
     * @return float|int
     */
    public function getTotalWeightAttribute(): float|int
    {
        if ($this->items->isEmpty()) {
            return 0;
        }

        return WeightCalculator::fromCartItems($this->items)->inKgs();
    }

    /**
     * Retrieve the Total Display attribute.
     *
     * @return string
     */
    public function getTotalDisplayAttribute(): string
    {
        // $pattern = '/([\d,]+)(\d+)(.*)$/';
        return preg_replace('/\.([0-9]*)/', '<sup>$1</sup>', $this->total);
    }

    public static function mergeCarts(Cart $sourceCart, Cart $targetCart): void
    {
        $targetCart->items()
            ->saveMany($targetCart->items->merge($sourceCart->items));

        $targetCart->updateTotals();

        $targetCart->save();
    }

    /**
     * Make sure we delete all the items when the cart gets deleted
     * by hooking in the boot function.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($cart) {
            foreach ($cart->items as $cartItem) {
                $cartItem->delete();
            }
        });

        static::updating(function ($cart) {
            if ($cart->items()->count() <= 0) {
                $cart->delete();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('cart-config.user_model'), 'auth_user');
    }
}
