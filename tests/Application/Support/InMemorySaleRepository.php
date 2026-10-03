<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Model\DateRange;
use App\Application\Model\Page;
use App\Application\Model\PageRequest;
use App\Application\Ports\Outbound\SaleRepository;
use App\Domain\Model\Sale;
use App\Domain\ValueObject\SaleId;

final class InMemorySaleRepository implements SaleRepository
{
    /** @var array<string, Sale> */
    private array $sales = [];

    public function find(SaleId $id): ?Sale
    {
        return $this->sales[$id->value()] ?? null;
    }

    public function search(DateRange $range, PageRequest $request): Page
    {
        $items = $this->inRange($range);
        $total = count($items);
        $items = array_slice($items, $request->offset(), $request->size());

        return Page::of($items, $total);
    }

    public function listByRange(DateRange $range): array
    {
        return $this->inRange($range);
    }

    public function add(Sale $sale): void
    {
        $this->sales[$sale->id()->value()] = $sale;
    }

    /**
     * @return list<Sale>
     */
    private function inRange(DateRange $range): array
    {
        $items = array_values(array_filter(
            $this->sales,
            static fn (Sale $sale): bool => $range->contains($sale->soldAt()),
        ));

        usort($items, static fn (Sale $a, Sale $b): int => $a->soldAt() <=> $b->soldAt());

        return $items;
    }
}
