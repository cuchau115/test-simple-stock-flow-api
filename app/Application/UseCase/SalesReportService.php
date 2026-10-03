<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Model\DateRange;
use App\Application\Ports\Inbound\GetSalesReport;
use App\Application\Ports\Inbound\SalesReport;
use App\Application\Ports\Inbound\SalesReportRow;
use App\Application\Ports\Outbound\SalesAggregateRow;
use App\Application\Ports\Outbound\SalesReportQuery;

/**
 * The report is aggregated in the engine (CA-06.5). This service never loads
 * the sales of the range: it only reshapes the rows the query already summed.
 */
final class SalesReportService implements GetSalesReport
{
    public function __construct(
        private readonly SalesReportQuery $query,
    ) {
    }

    public function report(DateRange $range): SalesReport
    {
        $aggregate = $this->query->aggregate($range);

        $rows = array_map(
            static fn (SalesAggregateRow $row): SalesReportRow => new SalesReportRow(
                $row->productId,
                $row->productName,
                $row->categoryName,
                $row->unitsSold,
                $row->revenue,
            ),
            $aggregate->rows,
        );

        return new SalesReport(
            $range->from(),
            $range->to(),
            $aggregate->salesCount,
            $aggregate->grandTotal,
            $rows,
        );
    }
}
