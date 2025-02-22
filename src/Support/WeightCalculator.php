<?php

namespace Feraandrei1\Cart\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

class WeightCalculator
{
    protected Collection $productIds;

    protected int $weight = 0;

    protected bool $hasTotalWeight = true;

    protected bool $wasCalculated = false;

    protected string $modelKey;

    public function __construct(protected Collection $products, protected bool $fromProducts = true)
    {
        $this->modelKey = $fromProducts ? 'product_id' : 'model_id';
        $this->productIds = $this->products->pluck($this->modelKey);
        $this->calculate();
    }

    public static function fromCartItems(Collection $items): self
    {
        return new static($items, false);
    }

    public static function fromProducts(Collection $products): self
    {
        return new static($products, true);
    }

    protected function getProductWeights(): Collection
    {
        return Product::whereIn('id', $this->productIds)
            ->simple()
            ->pluck('weight', 'id')
            ->map(fn ($item, $key) => ['product_id' => $key, 'weight' => $item])
            ->values();
    }

    public function inKgs(): float
    {
        return ceil($this->weight / 1000);
    }

    public function inGrams(): float
    {
        return $this->weight;
    }

    protected function calculate(): int
    {
        $productWeights = $this->getProductWeights();

        $products = $this->products->map(function ($item) use ($productWeights) {
            $productWeight = $productWeights->firstWhere('product_id', $item[$this->modelKey])['weight'] ?? 0;

            return [
                'product_id' => $item[$this->modelKey],
                'weight' => $item['quantity'] * $productWeight,
            ];
        });

        foreach ($products as $product) {
            if ($product['weight'] === 0) {
                $this->hasTotalWeight = false;
            }

            $this->weight += $product['weight'];
        }

        return $this->hasTotalWeight ? ceil($this->weight) : 0;
    }
}
