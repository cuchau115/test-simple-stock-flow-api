<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Domain\ValueObject\Money;
use DateTimeImmutable;

final class SaleView
{
    /**
     * @param  list<SaleItemView>  $items
     */
    public function __construct(
        public readonly string $id,
        public readonly DateTimeImmutable $soldAt,
        public readonly string $soldBy,
        public readonly Money $total,
        public readonly array $items,
    ) {
    }

    public function currency(): string
    {
        return $this->total->currency();
    }
}
