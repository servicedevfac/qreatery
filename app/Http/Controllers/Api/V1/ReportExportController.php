<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\BuildRestaurantReportAction;
use App\Exports\RestaurantReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\Restaurant;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportExportController extends Controller
{
    public function pdf(
        ReportRequest $request,
        Restaurant $restaurant,
        BuildRestaurantReportAction $buildReport,
    ): Response {
        $report = $buildReport->handle($restaurant, $request->validated());
        $filename = 'rapport-'.$restaurant->slug.'-'.now()->format('Ymd-His').'.pdf';

        return Pdf::loadView('reports.overview', compact('restaurant', 'report'))
            ->setPaper('a4')
            ->download($filename);
    }

    public function xlsx(
        ReportRequest $request,
        Restaurant $restaurant,
        BuildRestaurantReportAction $buildReport,
    ): BinaryFileResponse {
        $report = $buildReport->handle($restaurant, $request->validated());
        $filename = 'rapport-'.$restaurant->slug.'-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new RestaurantReportExport($report), $filename);
    }
}
