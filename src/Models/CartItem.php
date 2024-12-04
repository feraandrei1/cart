<?php

namespace Niladam\Cart\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $with = ['product'];

    /**
     * The attributes that are mass assignable.
     *
     *
     * @var array
     */
    protected $fillable = [
        'cart_id', 'model_type', 'model_id', 'name', 'price', 'price_with_tax', 'tax', 'image', 'quantity', 'source',
    ];

    /**
     * Relationship with the Cart model.
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * This method is put to convert snake case in camelCase.
     *
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            // 'modelType' => $this->model_type,
            // 'modelId' => $this->model_id,
            'name' => $this->name,
            'price' => $this->price,
            'tax' => $this->tax,
            'price_with_tax' => $this->price_with_tax,
            'product_id' => $this->product->id,
            'image' => $this->product->coverImage(),
            'link' => $this->product->link(),
            'quantity' => $this->quantity,
            'item_price' => $this->item_price,
            'item_price_with_tax' => $this->item_price_with_tax,
            'product_tax' => $this->product_tax,
            'display_price' => $this->display_price,
            'fragile' => $this->product?->fragile,
        ];
    }

    /**
     * Relationships morphing to this Product model.
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo($this->model_type, 'model_type', 'model_id');
    }

    /**
     * Retrieve the Display Price attribute.
     *
     * @param    mixed
     * @param  mixed  $value
     * @return string
     */
    public function getDisplayPriceAttribute($value)
    {
        $pattern = '/([\d,]+)(\d+)(.*)$/';

        // return preg_replace($pattern, '$1<sup>$2</sup>$3', $this->pret);
        return preg_replace('/\.([0-9]*)/', '<sup>$1</sup>', $this->price_with_tax);
    }

    /**
     * Retrieve the Item Price attribute.
     *
     *
     * @param  bool  $tax
     * @return int
     */
    public function getItemPriceAttribute($tax = false)
    {
        if (! $tax) {
            return $this->price * $this->quantity;
        }

        return $this->price_with_tax * $this->quantity;
    }

    /**
     * Retrieve the Item Price With Tax attribute.
     *
     *
     * @return string
     */
    public function getItemPriceWithTaxAttribute()
    {
        return $this->getItemPriceAttribute(true);
    }

    /**
     * Retrieve the Product Tax attribute.
     *
     *
     * @return string
     */
    public function getProductTaxAttribute()
    {
        return number_format($this->item_price_with_tax - $this->item_price, 2);
    }

    /**
     * Retrieve the Link attribute.
     *
     *
     * @return string
     */
    public function getLinkAttribute()
    {
        return $this->product->link();
    }
}
