<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RestaurantReportExport implements Export, WithMultipleSheets
{
    /** @param array<string, mixed> $report */
    public function __construct(private readonly array $report) {}

    public function sheets(): array
    {
        return [
            new ReportSummarySheet($this->report),
            new TopProductsSheet($this->report['top_products']),
        ];
    }
}
