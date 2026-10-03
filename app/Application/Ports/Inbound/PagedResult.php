<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\Model\PageRequest;

final class PagedResult
{
    /**
     * @param  list<mixed>  $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $page,
        public readonly int $size,
        public readonly int $total,
        public readonly int $totalPages,
    ) {
    }

    /**
     * @param  list<mixed>  $items
     */
    public static function of(array $items, PageRequest $request, int $total): self
    {
        return new self(
            $items,
            $request->page(),
            $request->size(),
            $total,
            $request->totalPages($total),
        );
    }
}
