<?php

declare(strict_types=1);

namespace Tests\Application\Model;

use App\Application\Model\PageRequest;
use PHPUnit\Framework\TestCase;

final class PageRequestTest extends TestCase
{
    public function test_it_serves_the_defaults_when_nothing_is_given(): void
    {
        $request = PageRequest::of();

        self::assertSame(1, $request->page());
        self::assertSame(20, $request->size());
    }

    public function test_a_page_below_one_is_served_as_one(): void
    {
        self::assertSame(1, PageRequest::of(0)->page());
        self::assertSame(1, PageRequest::of(-3)->page());
    }

    public function test_a_non_positive_size_is_served_as_the_default(): void
    {
        self::assertSame(20, PageRequest::of(null, 0)->size());
        self::assertSame(20, PageRequest::of(null, -5)->size());
    }

    public function test_a_size_above_the_maximum_is_clipped_to_the_maximum(): void
    {
        self::assertSame(100, PageRequest::of(null, 101)->size());
        self::assertSame(100, PageRequest::of(null, 999)->size());
    }

    public function test_a_size_within_range_is_served_as_requested(): void
    {
        self::assertSame(1, PageRequest::of(null, 1)->size());
        self::assertSame(100, PageRequest::of(null, 100)->size());
    }

    public function test_the_offset_is_computed_from_the_served_page_and_size(): void
    {
        self::assertSame(0, PageRequest::of(1, 20)->offset());
        self::assertSame(40, PageRequest::of(3, 20)->offset());
    }

    public function test_total_pages_is_rounded_up(): void
    {
        self::assertSame(0, PageRequest::of(1, 20)->totalPages(0));
        self::assertSame(1, PageRequest::of(1, 20)->totalPages(1));
        self::assertSame(2, PageRequest::of(1, 20)->totalPages(40));
        self::assertSame(3, PageRequest::of(1, 20)->totalPages(41));
    }
}
