<?php

declare(strict_types=1);

namespace Tests\Domain\Model;

use App\Domain\Exception\CurrencyMismatchException;
use App\Domain\Exception\InsufficientStockException;
use App\Domain\Exception\InvalidNameException;
use App\Domain\Exception\InvalidPriceException;
use App\Domain\Exception\InvalidStockException;
use App\Domain\Model\Product;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    private const string CATEGORY_ID = '00000000-0000-4000-8000-0000000000c1';

    private function makeProduct(int $stock = 10, string $price = '100.00', ?string $imageKey = null): Product
    {
        return Product::create(
            ProductId::generate(),
            'Tornillo',
            Money::of($price),
            $stock,
            CategoryId::of(self::CATEGORY_ID),
            $imageKey,
        );
    }

    public function test_rn_01_withdrawing_more_than_available_fails_and_keeps_the_stock(): void
    {
        $product = $this->makeProduct(stock: 5);

        try {
            $product->withdraw(Quantity::of(6));
            self::fail('Se esperaba InsufficientStockException');
        } catch (InsufficientStockException) {
        }

        self::assertSame(5, $product->stock());
    }

    public function test_rn_01_withdrawing_the_whole_stock_leaves_zero(): void
    {
        $product = $this->makeProduct(stock: 5);

        $product->withdraw(Quantity::of(5));

        self::assertSame(0, $product->stock());
    }

    public function test_rn_02_a_zero_price_is_rejected(): void
    {
        $this->expectException(InvalidPriceException::class);

        $this->makeProduct(price: '0.00');
    }

    public function test_rn_02_changing_the_price_to_zero_is_rejected(): void
    {
        $product = $this->makeProduct();

        $this->expectException(InvalidPriceException::class);

        $product->changePrice(Money::of('0.00'));
    }

    public function test_rn_09_a_foreign_currency_price_is_rejected_on_creation(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Product::create(
            ProductId::generate(),
            'Tornillo',
            Money::of('100.00', 'USD'),
            10,
            CategoryId::of(self::CATEGORY_ID),
        );
    }

    public function test_rn_09_a_foreign_currency_price_is_rejected_on_change(): void
    {
        $product = $this->makeProduct();

        $this->expectException(CurrencyMismatchException::class);

        $product->changePrice(Money::of('100.00', 'USD'));
    }

    public function test_a_negative_initial_stock_is_rejected(): void
    {
        $this->expectException(InvalidStockException::class);

        $this->makeProduct(stock: -1);
    }

    public function test_a_blank_name_is_rejected(): void
    {
        $this->expectException(InvalidNameException::class);

        Product::create(
            ProductId::generate(),
            '   ',
            Money::of(10),
            1,
            CategoryId::of(self::CATEGORY_ID),
        );
    }

    public function test_a_blank_image_key_becomes_null(): void
    {
        $product = $this->makeProduct(imageKey: '   ');

        self::assertNull($product->imageKey());
    }
}
