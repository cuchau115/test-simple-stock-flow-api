<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

final class PlaceSaleLine
{
    public function __construct(
        public readonly string $productId,
        public readonly int $quantity,
    ) {
    }
}
