<?php

declare(strict_types=1);

namespace App\Application\Model;

/**
 * A page of domain objects plus the total count that matches the filter.
 *
 * Repositories return this and never the HTTP-shaped `PagedResult`: turning it
 * into a response is the use case's job, so the persistence adapter never
 * speaks the language of the contract.
 */
final class Page
{
    /**
     * @param  array<array-key, mixed>  $items
     */
    public function __construct(
        private readonly array $items,
        private readonly int $total,
    ) {
    }

    /**
     * @param  array<array-key, mixed>  $items
     */
    public static function of(array $items, int $total): self
    {
        return new self($items, $total);
    }

    public static function empty(): self
    {
        return new self([], 0);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->total;
    }
}
