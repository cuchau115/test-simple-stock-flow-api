<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Stringable;

final class SaleItemId implements Stringable
{
    private function __construct(
        private readonly Uuid $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::generate());
    }

    public static function of(string $value): self
    {
        return new self(Uuid::of($value));
    }

    public function value(): string
    {
        return $this->value->value();
    }

    public function equals(self $other): bool
    {
        return $this->value->equals($other->value);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
