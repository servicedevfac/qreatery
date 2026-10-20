<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\OrderItem;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __invoke(ReportRequest $request, Restaurant $restaurant): JsonResponse
    {
        $from = $request->date('date_from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('date_to')?->endOfDay() ?? now()->endOfDay();
        $mode = $request->validated('mode');

        $orders = $restaurant->orders()
            ->whereBetween('created_at', [$from, $to])
            ->when($mode, fn ($query) => $query->where('mode', $mode));

        $payments = $restaurant->payments()
            ->where('status', 'succeeded')
            ->whereBetween('processed_at', [$from, $to]);

        $chargesMinor = (int) (clone $payments)->where('type', 'charge')->sum('amount_minor');
        $refundsMinor = (int) (clone $payments)->where('type', 'refund')->sum('amount_minor');
        $orderCount = (clone $orders)->count();
        $averageBasketMinor = $orderCount === 0 ? 0 : (int) round((float) (clone $orders)->avg('total_minor'));
        $preparationDurations = (clone $orders)
            ->whereNotNull('accepted_at')
            ->whereNotNull('ready_at')
            ->get(['accepted_at', 'ready_at'])
            ->map(fn ($order): int => $order->accepted_at->diffInSeconds($order->ready_at));

        $ordersByMode = (clone $orders)
            ->selectRaw('mode, COUNT(*) as aggregate')
            ->groupBy('mode')
            ->pluck('aggregate', 'mode')
            ->map(fn ($count): int => (int) $count);

        $topProducts = OrderItem::query()
            ->whereHas('order', function ($query) use ($restaurant, $from, $to, $mode): void {
                $query->whereBelongsTo($restaurant)
                    ->whereBetween('created_at', [$from, $to])
                    ->when($mode, fn ($modeQuery) => $modeQuery->where('mode', $mode));
            })
            ->selectRaw('product_name, SUM(quantity) as quantity, SUM(line_total_minor) as revenue_minor')
            ->groupBy('product_name')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn ($item): array => [
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'revenue_minor' => (int) $item->revenue_minor,
            ]);

        return response()->json(['data' => [
            'period' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
                'timezone' => $restaurant->timezone,
                'mode' => $mode,
            ],
            'orders' => [
                'total' => $orderCount,
                'accepted' => (clone $orders)->whereNotNull('accepted_at')->count(),
                'completed' => (clone $orders)->where('preparation_status', 'completed')->count(),
                'cancelled' => (clone $orders)->where('preparation_status', 'cancelled')->count(),
                'rejected' => (clone $orders)->where('preparation_status', 'rejected')->count(),
                'average_basket_minor' => $averageBasketMinor,
                'by_mode' => $ordersByMode,
                'average_preparation_seconds' => $preparationDurations->isEmpty() ? null : (int) round($preparationDurations->average()),
            ],
            'payments' => [
                'charged_minor' => $chargesMinor,
                'refunded_minor' => $refundsMinor,
                'net_collected_minor' => $chargesMinor - $refundsMinor,
                'currency' => $restaurant->currency,
            ],
            'top_products' => $topProducts,
        ]]);
    }
}
