<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Domain\ValueObject\Money;

final class ProductView
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly Money $price,
        public readonly int $stock,
        public readonly string $categoryId,
        public readonly string $categoryName,
        public readonly ?string $imageUrl,
    ) {
    }

    public function currency(): string
    {
        return $this->price->currency();
    }
}
