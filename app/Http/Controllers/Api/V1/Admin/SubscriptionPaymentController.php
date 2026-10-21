<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\RecordSubscriptionPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionPaymentRequest;
use App\Http\Resources\SubscriptionPaymentResource;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionPaymentController extends Controller
{
    public function index(Subscription $subscription): AnonymousResourceCollection
    {
        return SubscriptionPaymentResource::collection(
            $subscription->payments()->with('recorder')->latest('id')->paginate()
        );
    }

    public function store(StoreSubscriptionPaymentRequest $request, Subscription $subscription, RecordSubscriptionPaymentAction $recordPayment): JsonResponse
    {
        $payment = $recordPayment->handle($subscription, $request->user(), $request->validated());

        return (new SubscriptionPaymentResource($payment))->response()->setStatusCode(201);
    }
}
