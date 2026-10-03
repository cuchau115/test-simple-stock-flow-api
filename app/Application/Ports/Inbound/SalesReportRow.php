<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Domain\ValueObject\Money;

final class SalesReportRow
{
    public function __construct(
        public readonly string $productId,
        public readonly string $productName,
        public readonly string $categoryName,
        public readonly int $unitsSold,
        public readonly Money $revenue,
    ) {
    }
}
