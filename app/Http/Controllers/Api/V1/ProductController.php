<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\SaveProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('view', $restaurant);

        $products = $restaurant->products()
            ->with(['category', 'variants', 'optionGroups.options'])
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->toString().'%'))
            ->orderBy('position')
            ->orderBy('name')
            ->paginate();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request, Restaurant $restaurant, SaveProductAction $saveProduct): JsonResponse
    {
        $product = $saveProduct->handle($restaurant, $request->validated());

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function show(Restaurant $restaurant, Product $product): ProductResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $product);
        Gate::authorize('view', $restaurant);

        return new ProductResource($product->load(['category', 'variants', 'optionGroups.options']));
    }

    public function update(UpdateProductRequest $request, Restaurant $restaurant, Product $product, SaveProductAction $saveProduct): ProductResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $product);

        return new ProductResource($saveProduct->handle($restaurant, $request->validated(), $product));
    }

    public function destroy(Restaurant $restaurant, Product $product): Response
    {
        $this->ensureBelongsToRestaurant($restaurant, $product);
        Gate::authorize('manageMenu', $restaurant);
        $product->update(['status' => 'archived', 'is_available' => false]);

        return response()->noContent();
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, Product $product): void
    {
        abort_unless($product->restaurant_id === $restaurant->id, 404);
    }
}
