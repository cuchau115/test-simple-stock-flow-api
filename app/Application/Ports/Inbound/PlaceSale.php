<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Domain\ValueObject\SaleId;

interface PlaceSale
{
    public function place(PlaceSaleCommand $command, AuthenticatedUser $soldBy): SaleId;
}
