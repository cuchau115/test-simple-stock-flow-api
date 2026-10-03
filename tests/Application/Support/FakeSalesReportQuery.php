<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Model\DateRange;
use App\Application\Ports\Outbound\SalesAggregate;
use App\Application\Ports\Outbound\SalesReportQuery;

final class FakeSalesReportQuery implements SalesReportQuery
{
    private ?SalesAggregate $aggregate = null;

    public function willReturn(SalesAggregate $aggregate): void
    {
        $this->aggregate = $aggregate;
    }

    public function aggregate(DateRange $range): SalesAggregate
    {
        return $this->aggregate ?? SalesAggregate::empty();
    }
}
