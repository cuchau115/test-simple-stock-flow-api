<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidUsernameException;
use Stringable;

/**
 * Username normalized to lowercase and trimmed (RN-10).
 *
 * The database index is case- and accent-insensitive; normalizing here is what
 * makes the comparison in the application agree with the one in the engine.
 */
final class Username implements Stringable
{
    private function __construct(
        private readonly string $value,
    ) {
    }

    public static function of(string $value): self
    {
        $normalized = mb_strtolower(trim($value));

        if ($normalized === '') {
            throw InvalidUsernameException::blank();
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
