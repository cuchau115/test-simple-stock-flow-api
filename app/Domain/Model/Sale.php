<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\EmptySaleException;
use App\Domain\Exception\InvalidNameException;
use App\Domain\Exception\RepeatedProductException;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\SaleId;
use App\Domain\ValueObject\SaleItemId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;
use DateTimeImmutable;

/**
 * Aggregate root of sales.
 *
 * Registered sales are immutable: there is no operation to edit or delete, and
 * the item collection can only grow through `addItem` (RN-07). The total is
 * computed from the lines, never stored (RN-12).
 */
final class Sale
{
    /** @var list<SaleItem> */
    private array $items = [];

    private function __construct(
        private readonly SaleId $id,
        private readonly DateTimeImmutable $soldAt,
        private readonly Username $soldByUsername,
        private readonly UserId $soldByUserId,
    ) {
    }

    public static function create(
        SaleId $id,
        DateTimeImmutable $soldAt,
        Username $soldByUsername,
        UserId $soldByUserId,
    ): self {
        return new self($id, $soldAt, $soldByUsername, $soldByUserId);
    }

    public function addItem(Product $product, Quantity $quantity, string $categoryName): SaleItem
    {
        $this->ensureProductNotRepeated($product->id());

        $categoryName = trim($categoryName);

        if ($categoryName === '') {
            throw InvalidNameException::category();
        }

        $product->withdraw($quantity);

        $item = SaleItem::create(
            SaleItemId::generate(),
            $product->id(),
            $product->name(),
            $categoryName,
            $quantity,
            $product->price(),
        );

        $this->items[] = $item;

        return $item;
    }

    public function ensureConfirmable(): void
    {
        if ($this->items === []) {
            throw EmptySaleException::cannotConfirm();
        }
    }

    public function total(): Money
    {
        $total = Money::zero();

        foreach ($this->items as $item) {
            $total = $total->plus($item->subtotal());
        }

        return $total;
    }

    /** @return list<SaleItem> */
    public function items(): array
    {
        return $this->items;
    }

    public function id(): SaleId
    {
        return $this->id;
    }

    public function soldAt(): DateTimeImmutable
    {
        return $this->soldAt;
    }

    public function soldByUsername(): Username
    {
        return $this->soldByUsername;
    }

    public function soldByUserId(): UserId
    {
        return $this->soldByUserId;
    }

    private function ensureProductNotRepeated(ProductId $productId): void
    {
        foreach ($this->items as $item) {
            if ($item->productId()->equals($productId)) {
                throw RepeatedProductException::inSale();
            }
        }
    }
}
