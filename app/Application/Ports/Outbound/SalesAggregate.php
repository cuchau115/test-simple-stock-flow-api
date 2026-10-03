<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\ValueObject\Money;

final class SalesAggregate
{
    /**
     * @param  list<SalesAggregateRow>  $rows
     */
    public function __construct(
        public readonly int $salesCount,
        public readonly Money $grandTotal,
        public readonly array $rows,
    ) {
    }

    public static function empty(): self
    {
        return new self(0, Money::zero(), []);
    }
}
