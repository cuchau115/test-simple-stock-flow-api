<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\Model\DateRange;
use App\Application\Model\PageRequest;

interface GetSales
{
    public function get(string $saleId): SaleView;

    public function list(DateRange $range, PageRequest $request): PagedResult;
}
