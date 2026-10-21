<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\RecordAuditLogAction;
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

    public function store(
        StoreProductRequest $request,
        Restaurant $restaurant,
        SaveProductAction $saveProduct,
        RecordAuditLogAction $recordAuditLog,
    ): JsonResponse {
        $product = $saveProduct->handle($restaurant, $request->validated());
        $recordAuditLog->handle($request->user(), 'product.created', $product, $restaurant, [
            'price_minor' => $product->price_minor,
            'currency' => $restaurant->currency,
        ]);

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function show(Restaurant $restaurant, Product $product): ProductResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $product);
        Gate::authorize('view', $restaurant);

        return new ProductResource($product->load(['category', 'variants', 'optionGroups.options']));
    }

    public function update(
        UpdateProductRequest $request,
        Restaurant $restaurant,
        Product $product,
        SaveProductAction $saveProduct,
        RecordAuditLogAction $recordAuditLog,
    ): ProductResource {
        $this->ensureBelongsToRestaurant($restaurant, $product);
        $oldPriceMinor = $product->price_minor;
        $updatedProduct = $saveProduct->handle($restaurant, $request->validated(), $product);
        $recordAuditLog->handle($request->user(), 'product.updated', $updatedProduct, $restaurant, [
            'old_price_minor' => $oldPriceMinor,
            'new_price_minor' => $updatedProduct->price_minor,
            'currency' => $restaurant->currency,
        ]);

        return new ProductResource($updatedProduct);
    }

    public function destroy(
        Request $request,
        Restaurant $restaurant,
        Product $product,
        RecordAuditLogAction $recordAuditLog,
    ): Response {
        $this->ensureBelongsToRestaurant($restaurant, $product);
        Gate::authorize('manageMenu', $restaurant);
        $product->update(['status' => 'archived', 'is_available' => false]);
        $recordAuditLog->handle($request->user(), 'product.archived', $product, $restaurant);

        return response()->noContent();
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, Product $product): void
    {
        abort_unless($product->restaurant_id === $restaurant->id, 404);
    }
}
