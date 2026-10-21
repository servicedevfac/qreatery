<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $plans = Plan::query()
            ->when($request->filled('active'), fn ($query) => $query->where('is_active', $request->boolean('active')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $search)->orWhere('slug', 'like', $search));
            })
            ->orderBy('price_minor')
            ->paginate();

        return PlanResource::collection($plans);
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? $this->uniqueSlug($data['name']);
        $data['currency'] = strtoupper($data['currency'] ?? 'XOF');
        $plan = Plan::query()->create($data);

        return (new PlanResource($plan))->response()->setStatusCode(201);
    }

    public function show(Plan $plan): PlanResource
    {
        return new PlanResource($plan);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): PlanResource
    {
        $data = $request->validated();
        if (isset($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }
        $plan->update($data);

        return new PlanResource($plan->refresh());
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $suffix = 2;

        while (Plan::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
