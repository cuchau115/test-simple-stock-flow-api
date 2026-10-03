<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Ports\Outbound\Token;
use App\Application\Ports\Outbound\TokenGenerator;
use App\Domain\Model\User;
use DateTimeImmutable;

final class FakeTokenGenerator implements TokenGenerator
{
    public function __construct(
        private readonly string $value = 'token-de-prueba',
        private readonly ?DateTimeImmutable $expiresAt = null,
    ) {
    }

    public function generate(User $user): Token
    {
        return new Token(
            $this->value,
            $this->expiresAt ?? new DateTimeImmutable('2030-01-01T00:00:00+00:00'),
        );
    }
}
