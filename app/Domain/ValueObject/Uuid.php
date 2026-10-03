<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidIdentifierException;
use Stringable;

/**
 * Every identifier in the system is a UUID in text (D-01). Validating the shape
 * at the edge of the domain turns a malformed reference into a business rule
 * violation (422) instead of an engine error.
 */
final class Uuid implements Stringable
{
    private const string PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';

    private function __construct(
        private readonly string $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(self::format(random_bytes(16)));
    }

    public static function of(string $value): self
    {
        $value = mb_strtolower(trim($value));

        if ($value === '') {
            throw InvalidIdentifierException::blank();
        }

        if (preg_match(self::PATTERN, $value) !== 1) {
            throw InvalidIdentifierException::notAUuid($value);
        }

        return new self($value);
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

    private static function format(string $bytes): string
    {
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
