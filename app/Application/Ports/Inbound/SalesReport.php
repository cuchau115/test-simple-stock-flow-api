<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Domain\ValueObject\Money;
use DateTimeImmutable;

final class SalesReport
{
    /**
     * @param  list<SalesReportRow>  $rows
     */
    public function __construct(
        public readonly DateTimeImmutable $from,
        public readonly DateTimeImmutable $to,
        public readonly int $salesCount,
        public readonly Money $grandTotal,
        public readonly array $rows,
    ) {
    }

    public function currency(): string
    {
        return $this->grandTotal->currency();
    }
}
