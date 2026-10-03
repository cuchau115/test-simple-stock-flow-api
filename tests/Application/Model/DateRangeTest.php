<?php

declare(strict_types=1);

namespace Tests\Application\Model;

use App\Application\Model\DateRange;
use App\Application\Exception\InvalidDateRangeException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DateRangeTest extends TestCase
{
    private function instant(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value);
    }

    public function test_it_rejects_a_to_before_a_from(): void
    {
        $this->expectException(InvalidDateRangeException::class);

        DateRange::of(
            $this->instant('2026-12-01T00:00:00+00:00'),
            $this->instant('2026-01-01T00:00:00+00:00'),
        );
    }

    public function test_an_equal_from_and_to_is_a_valid_empty_range(): void
    {
        $range = DateRange::of(
            $this->instant('2026-01-01T00:00:00+00:00'),
            $this->instant('2026-01-01T00:00:00+00:00'),
        );

        self::assertFalse($range->contains($this->instant('2026-01-01T00:00:00+00:00')));
    }

    public function test_from_is_inclusive_and_to_is_exclusive(): void
    {
        $range = DateRange::of(
            $this->instant('2026-01-01T00:00:00+00:00'),
            $this->instant('2026-02-01T00:00:00+00:00'),
        );

        self::assertTrue($range->contains($this->instant('2026-01-01T00:00:00+00:00')));
        self::assertTrue($range->contains($this->instant('2026-01-31T23:59:59+00:00')));
        self::assertFalse($range->contains($this->instant('2026-02-01T00:00:00+00:00')));
    }

    public function test_the_extremes_are_normalised_to_utc(): void
    {
        $range = DateRange::of(
            $this->instant('2026-01-01T02:00:00+02:00'),
            $this->instant('2026-01-02T02:00:00+02:00'),
        );

        self::assertSame('2026-01-01T00:00:00+00:00', $range->from()->format('c'));
        self::assertSame('2026-01-02T00:00:00+00:00', $range->to()->format('c'));
    }
}
