<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class KitchenTicketController extends Controller
{
    public function show(Restaurant $restaurant, Order $order): Response
    {
        abort_unless($order->restaurant_id === $restaurant->id, 404);
        Gate::authorize('viewOrders', $restaurant);

        $order->load(['restaurantTable', 'items.options']);

        $filename = 'bon-cuisine-'.$order->number.'.pdf';

        // 80mm width in points (80mm * 2.83465 ~= 226.77 pt), auto height or 600 pt
        return Pdf::loadView('orders.kitchen-ticket', compact('restaurant', 'order'))
            ->setPaper([0, 0, 226.77, 650], 'portrait')
            ->download($filename);
    }
}
