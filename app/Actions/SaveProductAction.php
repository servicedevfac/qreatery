<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaveProductAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Restaurant $restaurant, array $data, ?Product $product = null): Product
    {
        return DB::transaction(function () use ($restaurant, $data, $product): Product {
            $variants = Arr::pull($data, 'variants');
            $optionGroupIds = Arr::pull($data, 'option_group_ids');

            if (! $product instanceof Product) {
                $data['slug'] = $this->uniqueSlug($restaurant, (string) $data['name']);
                $product = $restaurant->products()->create($data);
            } else {
                $product->update($data);
            }

            if (is_array($variants)) {
                $product->variants()->delete();
                $product->variants()->createMany($variants);
            }

            if (is_array($optionGroupIds)) {
                $product->optionGroups()->sync($optionGroupIds);
            }

            return $product->load(['category', 'variants', 'optionGroups.options']);
        });
    }

    private function uniqueSlug(Restaurant $restaurant, string $name): string
    {
        $baseSlug = Str::slug($name) ?: Str::lower(Str::random(12));
        $slug = $baseSlug;
        $suffix = 2;

        while ($restaurant->products()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
