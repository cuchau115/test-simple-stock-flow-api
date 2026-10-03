<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\Model\DateRange;

interface GetSalesReport
{
    public function report(DateRange $range): SalesReport;
}
