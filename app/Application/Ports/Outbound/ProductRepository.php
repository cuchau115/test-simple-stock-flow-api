<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Application\Model\Page;
use App\Application\Model\PageRequest;
use App\Domain\Model\Product;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\ProductId;

interface ProductRepository
{
    public function save(Product $product): void;

    /**
     * Loads by identifier without the soft-delete filter: a historic sale line
     * must be able to resolve its product (ADR-003).
     */
    public function find(ProductId $id): ?Product;

    /**
     * Loads by identifier with the soft-delete filter: a withdrawn product can
     * neither be sold nor listed.
     */
    public function findActive(ProductId $id): ?Product;

    /**
     * Batch load for selling, with the soft-delete filter.
     *
     * @param  list<ProductId>  $ids
     * @return list<Product>
     */
    public function findActiveByIds(array $ids): array;

    public function search(?string $search, ?CategoryId $categoryId, PageRequest $request): Page;

    /**
     * Soft delete: writes `deleted_at`. The hard delete is deliberately absent
     * so it is not reachable from any use case (ADR-003, RN-08).
     */
    public function delete(ProductId $id): void;
}
