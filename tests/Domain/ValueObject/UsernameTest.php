<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\Exception\InvalidUsernameException;
use App\Domain\ValueObject\Username;
use PHPUnit\Framework\TestCase;

final class UsernameTest extends TestCase
{
    public function test_rn_10_it_trims_and_lowercases(): void
    {
        self::assertSame('ana', Username::of('  Ana  ')->value());
    }

    public function test_rn_10_it_rejects_a_blank_value(): void
    {
        $this->expectException(InvalidUsernameException::class);

        Username::of('   ');
    }
}
