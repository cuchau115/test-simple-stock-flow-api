<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidQuantityException;

final class Quantity
{
    private function __construct(
        private readonly int $value,
    ) {
    }

    public static function of(int|string $value): self
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            if (! preg_match('/^\d+$/', $trimmed)) {
                throw InvalidQuantityException::notAnInteger($value);
            }

            $value = (int) $trimmed;
        }

        if ($value <= 0) {
            throw InvalidQuantityException::notPositive();
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
