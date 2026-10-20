<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicMenuResource;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class PublicMenuController extends Controller
{
    public function __invoke(Request $request, Restaurant $restaurant): PublicMenuResource
    {
        abort_unless($restaurant->status->value === 'active', 404);

        $restaurant->load(['categories' => function ($query) use ($request): void {
            $query->where('is_active', true)
                ->whereNull('archived_at')
                ->whereHas('products', function ($productQuery) use ($request): void {
                    $productQuery->where('status', 'active')
                        ->where('is_available', true)
                        ->when($request->filled('search'), fn ($searchQuery) => $searchQuery->where('name', 'like', '%'.$request->string('search')->toString().'%'));
                })
                ->with(['products' => function ($productQuery) use ($request): void {
                    $productQuery->where('status', 'active')
                        ->where('is_available', true)
                        ->when($request->filled('search'), fn ($searchQuery) => $searchQuery->where('name', 'like', '%'.$request->string('search')->toString().'%'))
                        ->with([
                            'variants' => fn ($variantQuery) => $variantQuery->where('is_active', true)->orderBy('position'),
                            'optionGroups' => fn ($groupQuery) => $groupQuery->where('is_active', true)->orderBy('position'),
                            'optionGroups.options' => fn ($optionQuery) => $optionQuery->where('is_active', true)->orderBy('position'),
                        ])
                        ->orderBy('position')
                        ->orderBy('name');
                }])
                ->orderBy('position')
                ->orderBy('name');
        }]);

        return new PublicMenuResource($restaurant);
    }
}
