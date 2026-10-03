<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use DateTimeImmutable;

final class Token
{
    public function __construct(
        public readonly string $value,
        public readonly DateTimeImmutable $expiresAt,
    ) {
    }
}
