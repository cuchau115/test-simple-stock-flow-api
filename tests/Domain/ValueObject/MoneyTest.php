<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\Exception\CurrencyMismatchException;
use App\Domain\Exception\InvalidPriceException;
use App\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_it_rounds_to_two_decimals_half_up(): void
    {
        self::assertSame('10.56', (string) Money::of('10.555'));
        self::assertSame('10.55', (string) Money::of('10.554'));
    }

    public function test_it_uses_the_default_currency(): void
    {
        self::assertSame('COP', Money::of(10)->currency());
    }

    public function test_it_allows_zero(): void
    {
        self::assertTrue(Money::of(0)->isZero());
    }

    public function test_it_rejects_a_negative_amount(): void
    {
        $this->expectException(InvalidPriceException::class);

        Money::of('-0.01');
    }

    public function test_it_rejects_a_non_numeric_amount(): void
    {
        $this->expectException(InvalidPriceException::class);

        Money::of('no-es-un-numero');
    }

    public function test_rn_09_operating_on_two_different_currencies_fails(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::of(10, 'COP')->plus(Money::of(10, 'USD'));
    }

    public function test_it_adds_amounts_of_the_same_currency(): void
    {
        self::assertSame('30.00', (string) Money::of('10.00')->plus(Money::of('20.00')));
    }

    public function test_it_multiplies_by_an_integer_factor(): void
    {
        self::assertSame('30.00', (string) Money::of('10.00')->multipliedBy(3));
    }
}
