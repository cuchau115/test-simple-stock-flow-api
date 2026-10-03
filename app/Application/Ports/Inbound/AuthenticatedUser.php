<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

final class AuthenticatedUser
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $role,
    ) {
    }
}
