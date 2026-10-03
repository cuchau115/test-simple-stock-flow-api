<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\SaleItemId;

/**
 * Internal entity of the Sale aggregate. It does not exist outside its sale and
 * freezes the product name, the category name and the unit price at sale time
 * (RN-06). Only `Sale::addItem` builds legal instances.
 */
final class SaleItem
{
    private function __construct(
        private readonly SaleItemId $id,
        private readonly ProductId $productId,
        private readonly string $productName,
        private readonly string $categoryName,
        private readonly Quantity $quantity,
        private readonly Money $unitPrice,
    ) {
    }

    public static function create(
        SaleItemId $id,
        ProductId $productId,
        string $productName,
        string $categoryName,
        Quantity $quantity,
        Money $unitPrice,
    ): self {
        return new self($id, $productId, $productName, $categoryName, $quantity, $unitPrice);
    }

    public function subtotal(): Money
    {
        return $this->unitPrice->multipliedBy($this->quantity->value());
    }

    public function id(): SaleItemId
    {
        return $this->id;
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function categoryName(): string
    {
        return $this->categoryName;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }
}
