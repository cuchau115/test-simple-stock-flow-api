<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\CurrencyMismatchException;
use App\Domain\Exception\InvalidPriceException;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\RoundingMode;
use JsonSerializable;
use Stringable;

/**
 * Exact amount with its currency kept inside (D-05).
 *
 * The system is monocurrency, so every amount is built with DEFAULT_CURRENCY
 * unless an explicit one is given. Keeping the currency in the value object is
 * what lets the guard be a domain rule instead of a persistence accident.
 */
final class Money implements JsonSerializable, Stringable
{
    public const int SCALE = 2;

    public const string DEFAULT_CURRENCY = 'COP';

    private function __construct(
        private readonly BigDecimal $amount,
        private readonly string $currency,
    ) {
    }

    public static function of(int|string|BigDecimal $amount, ?string $currency = null): self
    {
        $currency = mb_strtoupper(trim($currency ?? self::DEFAULT_CURRENCY));

        if ($currency === '') {
            throw CurrencyMismatchException::expected(self::DEFAULT_CURRENCY, $currency);
        }

        try {
            $value = BigDecimal::of($amount);
        } catch (NumberFormatException) {
            throw InvalidPriceException::notANumber((string) $amount);
        }

        if ($value->isNegative()) {
            throw InvalidPriceException::negativeAmount();
        }

        return new self(
            $value->toScale(self::SCALE, RoundingMode::HALF_UP),
            $currency,
        );
    }

    public static function zero(?string $currency = null): self
    {
        return self::of(BigDecimal::zero(), $currency);
    }

    public function amount(): BigDecimal
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function isZero(): bool
    {
        return $this->amount->isZero();
    }

    public function isPositive(): bool
    {
        return $this->amount->isPositive();
    }

    public function plus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(
            $this->amount->plus($other->amount)->toScale(self::SCALE, RoundingMode::HALF_UP),
            $this->currency,
        );
    }

    public function multipliedBy(int $multiplier): self
    {
        return new self(
            $this->amount->multipliedBy($multiplier)->toScale(self::SCALE, RoundingMode::HALF_UP),
            $this->currency,
        );
    }

    public function isGreaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount->isGreaterThan($other->amount);
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency
            && $this->amount->isEqualTo($other->amount);
    }

    public function __toString(): string
    {
        return (string) $this->amount->toScale(self::SCALE);
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw CurrencyMismatchException::expected($this->currency, $other->currency);
        }
    }
}
