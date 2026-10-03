<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Model\Page;
use App\Application\Model\PageRequest;
use App\Application\Ports\Outbound\ProductRepository;
use App\Domain\Model\Product;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\ProductId;

final class InMemoryProductRepository implements ProductRepository
{
    /** @var array<string, Product> */
    private array $products = [];

    /** @var array<string, bool> */
    private array $deleted = [];

    /** @var list<string> */
    public array $savedIds = [];

    /**
     * Number of saves that will blow up with a concurrency conflict before
     * succeeding; used to exercise the retry loop of PlaceSaleService.
     */
    public int $failSaves = 0;

    public function __construct(Product ...$seed)
    {
        foreach ($seed as $product) {
            $this->products[$product->id()->value()] = $product;
        }
    }

    public function save(Product $product): void
    {
        if ($this->failSaves > 0) {
            $this->failSaves--;

            throw \App\Application\Exception\ConcurrencyConflict::afterRetries();
        }

        $this->savedIds[] = $product->id()->value();
        $this->products[$product->id()->value()] = $product;
        unset($this->deleted[$product->id()->value()]);
    }

    public function find(ProductId $id): ?Product
    {
        $product = $this->products[$id->value()] ?? null;

        return $product === null ? null : clone $product;
    }

    public function findActive(ProductId $id): ?Product
    {
        if (isset($this->deleted[$id->value()])) {
            return null;
        }

        return $this->find($id);
    }

    public function findActiveByIds(array $ids): array
    {
        $found = [];

        foreach ($ids as $id) {
            $product = $this->findActive($id);

            if ($product !== null) {
                $found[] = $product;
            }
        }

        return $found;
    }

    public function search(?string $search, ?CategoryId $categoryId, PageRequest $request): Page
    {
        $items = array_values(array_filter(
            $this->products,
            fn (Product $product): bool => ! isset($this->deleted[$product->id()->value()]),
        ));

        if ($search !== null && $search !== '') {
            $items = array_values(array_filter(
                $items,
                fn (Product $product): bool => mb_stripos($product->name(), $search) !== false,
            ));
        }

        if ($categoryId !== null) {
            $items = array_values(array_filter(
                $items,
                fn (Product $product): bool => $product->categoryId()->equals($categoryId),
            ));
        }

        $total = count($items);
        $items = array_map(static fn (Product $product): Product => clone $product, $items);
        $items = array_slice($items, $request->offset(), $request->size());

        return Page::of($items, $total);
    }

    public function delete(ProductId $id): void
    {
        $this->deleted[$id->value()] = true;
    }

    public function isDeleted(ProductId $id): bool
    {
        return isset($this->deleted[$id->value()]);
    }
}
