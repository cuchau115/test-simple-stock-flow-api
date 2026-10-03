<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Ports\Outbound\PasswordHasher;

/**
 * Deterministic, fast and reversible: a test can rebuild the expected hash.
 */
final class FakePasswordHasher implements PasswordHasher
{
    public function hash(string $plain): string
    {
        return 'hashed:'.$plain;
    }

    public function verify(string $plain, string $hash): bool
    {
        return $hash === $this->hash($plain);
    }
}
