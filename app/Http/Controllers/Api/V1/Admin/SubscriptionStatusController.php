<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\TransitionSubscriptionStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSubscriptionStatusRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\SubscriptionStatus;

class SubscriptionStatusController extends Controller
{
    public function __invoke(UpdateSubscriptionStatusRequest $request, Subscription $subscription, TransitionSubscriptionStatusAction $transition): SubscriptionResource
    {
        $data = $request->validated();
        $subscription = $transition->handle(
            $subscription,
            $request->user(),
            SubscriptionStatus::from($data['status']),
            $data['reason'] ?? null,
        );

        return new SubscriptionResource($subscription);
    }
}
