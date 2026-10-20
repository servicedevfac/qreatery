<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProductImageRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateProductImageRequest $request, Restaurant $restaurant, Product $product): ProductResource
    {
        abort_unless($product->restaurant_id === $restaurant->id, 404);

        if ($product->image_path !== null) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->update([
            'image_path' => $request->file('image')->store("products/{$restaurant->id}", 'public'),
        ]);

        return new ProductResource($product->refresh()->load(['category', 'variants', 'optionGroups.options']));
    }
}
