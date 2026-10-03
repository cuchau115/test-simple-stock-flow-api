<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Domain\ValueObject\Money;

final class SaleItemView
{
    public function __construct(
        public readonly string $productId,
        public readonly string $productName,
        public readonly int $quantity,
        public readonly Money $unitPrice,
        public readonly Money $subtotal,
    ) {
    }
}
