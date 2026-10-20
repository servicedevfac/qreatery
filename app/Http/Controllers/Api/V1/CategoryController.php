<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('view', $restaurant);

        $categories = $restaurant->categories()
            ->when(! $request->boolean('with_archived'), fn ($query) => $query->whereNull('archived_at'))
            ->orderBy('position')
            ->orderBy('name')
            ->paginate();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request, Restaurant $restaurant): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($restaurant, $data['name']);
        $category = $restaurant->categories()->create($data);

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function show(Restaurant $restaurant, Category $category): CategoryResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $category);
        Gate::authorize('view', $restaurant);

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Restaurant $restaurant, Category $category): CategoryResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $category);
        $category->update($request->validated());

        return new CategoryResource($category->refresh());
    }

    public function destroy(Restaurant $restaurant, Category $category): Response
    {
        $this->ensureBelongsToRestaurant($restaurant, $category);
        Gate::authorize('manageMenu', $restaurant);
        $category->update(['is_active' => false, 'archived_at' => now()]);

        return response()->noContent();
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, Category $category): void
    {
        abort_unless($category->restaurant_id === $restaurant->id, 404);
    }

    private function uniqueSlug(Restaurant $restaurant, string $name): string
    {
        $baseSlug = Str::slug($name) ?: Str::lower(Str::random(12));
        $slug = $baseSlug;
        $suffix = 2;

        while ($restaurant->categories()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
