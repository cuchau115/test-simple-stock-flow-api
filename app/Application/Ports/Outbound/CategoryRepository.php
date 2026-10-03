<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Model\Category;
use App\Domain\ValueObject\CategoryId;

interface CategoryRepository
{
    public function find(CategoryId $id): ?Category;

    /**
     * @param  list<CategoryId>  $ids
     * @return list<Category>
     */
    public function findManyByIds(array $ids): array;

    /**
     * All reference categories, ordered by name (D-C1).
     *
     * @return list<Category>
     */
    public function listAll(): array;
}
