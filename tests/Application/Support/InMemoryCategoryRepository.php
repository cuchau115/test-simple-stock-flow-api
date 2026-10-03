<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Ports\Outbound\CategoryRepository;
use App\Domain\Model\Category;
use App\Domain\ValueObject\CategoryId;

final class InMemoryCategoryRepository implements CategoryRepository
{
    /** @var array<string, Category> */
    private array $categories = [];

    public function __construct(Category ...$seed)
    {
        foreach ($seed as $category) {
            $this->categories[$category->id()->value()] = $category;
        }
    }

    public function find(CategoryId $id): ?Category
    {
        return $this->categories[$id->value()] ?? null;
    }

    public function findManyByIds(array $ids): array
    {
        $found = [];

        foreach ($ids as $id) {
            if (isset($this->categories[$id->value()])) {
                $found[] = $this->categories[$id->value()];
            }
        }

        return $found;
    }

    public function listAll(): array
    {
        $all = array_values($this->categories);

        usort($all, static fn (Category $a, Category $b): int => strcmp($a->name(), $b->name()));

        return $all;
    }
}
