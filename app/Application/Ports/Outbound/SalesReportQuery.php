<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Application\Model\DateRange;

interface SalesReportQuery
{
    public function aggregate(DateRange $range): SalesAggregate;
}
