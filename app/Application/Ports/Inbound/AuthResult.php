<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use DateTimeImmutable;

final class AuthResult
{
    public function __construct(
        public readonly string $accessToken,
        public readonly DateTimeImmutable $expiresAt,
        public readonly string $username,
        public readonly string $role,
    ) {
    }
}
