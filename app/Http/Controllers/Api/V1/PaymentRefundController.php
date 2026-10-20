<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\RefundPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRefundRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;

class PaymentRefundController extends Controller
{
    public function __invoke(StorePaymentRefundRequest $request, Restaurant $restaurant, Payment $payment, RefundPaymentAction $refundPayment): JsonResponse
    {
        abort_unless($payment->restaurant_id === $restaurant->id, 404);
        $refund = $refundPayment->handle($restaurant, $payment, $request->user(), $request->validated());

        return (new PaymentResource($refund))->response()->setStatusCode(201);
    }
}
