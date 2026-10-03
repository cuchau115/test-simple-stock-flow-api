<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\CurrencyMismatchException;
use App\Domain\Exception\InsufficientStockException;
use App\Domain\Exception\InvalidNameException;
use App\Domain\Exception\InvalidPriceException;
use App\Domain\Exception\InvalidStockException;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;

/**
 * Aggregate root of the catalogue.
 *
 * Five attributes and nothing more (DP-03): name, price, stock, category and
 * image. `deleted_at` and `version` are persistence-only markers and are not
 * part of this entity (D-03, D-04).
 */
final class Product
{
    private function __construct(
        private readonly ProductId $id,
        private string $name,
        private Money $price,
        private int $stock,
        private CategoryId $categoryId,
        private ?string $imageKey,
    ) {
    }

    public static function create(
        ProductId $id,
        string $name,
        Money $price,
        int $stock,
        CategoryId $categoryId,
        ?string $imageKey = null,
    ): self {
        $name = trim($name);

        if ($name === '') {
            throw InvalidNameException::product();
        }

        self::assertSellingCurrency($price);

        if (! $price->isPositive()) {
            throw InvalidPriceException::notPositive();
        }

        if ($stock < 0) {
            throw InvalidStockException::negativeInitial();
        }

        return new self($id, $name, $price, $stock, $categoryId, self::normalizeImageKey($imageKey));
    }

    public function rename(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            throw InvalidNameException::product();
        }

        $this->name = $name;
    }

    public function changePrice(Money $price): void
    {
        self::assertSellingCurrency($price);

        if (! $price->isPositive()) {
            throw InvalidPriceException::notPositive();
        }

        $this->price = $price;
    }

    public function changeCategory(CategoryId $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function withdraw(Quantity $quantity): void
    {
        if ($quantity->value() > $this->stock) {
            throw InsufficientStockException::forProduct($this->name, $this->stock, $quantity->value());
        }

        $this->stock -= $quantity->value();
    }

    public function restock(int $units): void
    {
        if ($units < 0) {
            throw InvalidStockException::negative();
        }

        $this->stock += $units;
    }

    public function attachImage(?string $imageKey): void
    {
        $this->imageKey = self::normalizeImageKey($imageKey);
    }

    public function id(): ProductId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function categoryId(): CategoryId
    {
        return $this->categoryId;
    }

    public function imageKey(): ?string
    {
        return $this->imageKey;
    }

    private static function assertSellingCurrency(Money $price): void
    {
        if ($price->currency() !== Money::DEFAULT_CURRENCY) {
            throw CurrencyMismatchException::expected(Money::DEFAULT_CURRENCY, $price->currency());
        }
    }

    private static function normalizeImageKey(?string $imageKey): ?string
    {
        if ($imageKey === null) {
            return null;
        }

        $imageKey = trim($imageKey);

        return $imageKey === '' ? null : $imageKey;
    }
}
