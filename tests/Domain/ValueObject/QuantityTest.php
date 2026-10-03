<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\Exception\InvalidQuantityException;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class QuantityTest extends TestCase
{
    public function test_rn_03_it_rejects_zero(): void
    {
        $this->expectException(InvalidQuantityException::class);

        Quantity::of(0);
    }

    public function test_rn_03_it_rejects_a_negative_value(): void
    {
        $this->expectException(InvalidQuantityException::class);

        Quantity::of(-1);
    }

    public function test_rn_03_it_keeps_a_positive_value(): void
    {
        self::assertSame(1, Quantity::of(1)->value());
    }

    public function test_it_rejects_a_non_integer_value(): void
    {
        $this->expectException(InvalidQuantityException::class);

        Quantity::of('1.5');
    }
}
