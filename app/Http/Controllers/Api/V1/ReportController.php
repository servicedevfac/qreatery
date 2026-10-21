<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\BuildRestaurantReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __invoke(
        ReportRequest $request,
        Restaurant $restaurant,
        BuildRestaurantReportAction $buildReport,
    ): JsonResponse {
        return response()->json(['data' => $buildReport->handle($restaurant, $request->validated())]);
    }
}
