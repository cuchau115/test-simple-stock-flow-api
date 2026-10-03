<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

use App\Application\Model\PageRequest;
use App\Domain\ValueObject\Money;

interface ManageProducts
{
    public function create(string $name, Money $price, int $stock, string $categoryId): string;

    public function update(string $productId, string $name, Money $price, int $stock, string $categoryId): void;

    public function delete(string $productId): void;

    public function get(string $productId): ProductView;

    public function list(?string $search, ?string $categoryId, PageRequest $request): PagedResult;

    public function attachImage(string $productId, string $contentType, string $binary): string;

    /**
     * @return list<CategoryView>
     */
    public function listCategories(): array;
}
