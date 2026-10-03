<?php

declare(strict_types=1);

namespace App\Application\Model;

/**
 * Normalised pagination request.
 *
 * The trimming is an application rule, not an adapter one: `page` below 1 is
 * served as 1, an absent or non-positive `size` is served as the default, and a
 * `size` above the maximum is clipped. The response must echo the served
 * values, never the requested ones.
 */
final class PageRequest
{
    public const int DEFAULT_PAGE = 1;

    public const int DEFAULT_SIZE = 20;

    public const int MAX_SIZE = 100;

    private function __construct(
        private readonly int $page,
        private readonly int $size,
    ) {
    }

    public static function of(?int $page = null, ?int $size = null): self
    {
        return new self(self::normalizePage($page), self::normalizeSize($size));
    }

    public function page(): int
    {
        return $this->page;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->size;
    }

    public function totalPages(int $total): int
    {
        if ($this->size === 0) {
            return 0;
        }

        return intdiv($total + $this->size - 1, $this->size);
    }

    private static function normalizePage(?int $page): int
    {
        return $page === null || $page < 1 ? self::DEFAULT_PAGE : $page;
    }

    private static function normalizeSize(?int $size): int
    {
        if ($size === null || $size < 1) {
            return self::DEFAULT_SIZE;
        }

        return $size > self::MAX_SIZE ? self::MAX_SIZE : $size;
    }
}
