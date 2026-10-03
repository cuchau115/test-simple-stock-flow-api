<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

final class CategoryView
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
