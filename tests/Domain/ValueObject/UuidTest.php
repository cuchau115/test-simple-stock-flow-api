<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\Exception\InvalidIdentifierException;
use App\Domain\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class UuidTest extends TestCase
{
    public function test_it_generates_a_valid_uuid(): void
    {
        $uuid = Uuid::generate();

        self::assertSame($uuid->value(), Uuid::of($uuid->value())->value());
    }

    public function test_it_accepts_a_known_valid_uuid(): void
    {
        $raw = '00000000-0000-4000-8000-0000000000c1';

        self::assertSame($raw, Uuid::of($raw)->value());
    }

    public function test_it_rejects_a_value_that_is_not_a_uuid(): void
    {
        $this->expectException(InvalidIdentifierException::class);

        Uuid::of('product-1');
    }

    public function test_it_rejects_a_blank_value(): void
    {
        $this->expectException(InvalidIdentifierException::class);

        Uuid::of('   ');
    }
}
