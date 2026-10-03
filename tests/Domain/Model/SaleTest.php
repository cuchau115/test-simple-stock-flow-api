<?php

declare(strict_types=1);

namespace Tests\Domain\Model;

use App\Domain\Exception\EmptySaleException;
use App\Domain\Exception\InsufficientStockException;
use App\Domain\Exception\RepeatedProductException;
use App\Domain\Model\Product;
use App\Domain\Model\Sale;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\SaleId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SaleTest extends TestCase
{
    private const string CATEGORY_ID = '00000000-0000-4000-8000-0000000000c1';

    private function makeProduct(int $stock = 10, string $price = '100.00', string $name = 'Tornillo'): Product
    {
        return Product::create(
            ProductId::generate(),
            $name,
            Money::of($price),
            $stock,
            CategoryId::of(self::CATEGORY_ID),
        );
    }

    private function makeSale(): Sale
    {
        return Sale::create(
            SaleId::generate(),
            new DateTimeImmutable('2026-10-03T12:00:00+00:00'),
            Username::of('vendedor'),
            UserId::generate(),
        );
    }

    public function test_rn_04_a_sale_without_lines_is_not_confirmable(): void
    {
        $this->expectException(EmptySaleException::class);

        $this->makeSale()->ensureConfirmable();
    }

    public function test_rn_04_a_sale_with_a_line_is_confirmable(): void
    {
        $sale = $this->makeSale();
        $sale->addItem($this->makeProduct(), Quantity::of(1), 'Ferretería');

        $sale->ensureConfirmable();

        self::assertCount(1, $sale->items());
    }

    public function test_rn_05_adding_the_same_product_twice_fails(): void
    {
        $sale = $this->makeSale();
        $product = $this->makeProduct();
        $sale->addItem($product, Quantity::of(1), 'Ferretería');

        $this->expectException(RepeatedProductException::class);

        $sale->addItem($product, Quantity::of(1), 'Ferretería');
    }

    public function test_rn_05_a_duplicate_does_not_withdraw_stock(): void
    {
        $sale = $this->makeSale();
        $product = $this->makeProduct(stock: 10);
        $sale->addItem($product, Quantity::of(2), 'Ferretería');

        try {
            $sale->addItem($product, Quantity::of(1), 'Ferretería');
        } catch (RepeatedProductException) {
        }

        self::assertSame(8, $product->stock());
    }

    public function test_rn_06_the_line_freezes_name_and_price_at_sale_time(): void
    {
        $sale = $this->makeSale();
        $product = $this->makeProduct(price: '100.00', name: 'Tornillo');

        $item = $sale->addItem($product, Quantity::of(2), 'Ferretería');

        $product->rename('Tornillo reforzado');
        $product->changePrice(Money::of('250.00'));

        self::assertSame('Tornillo', $item->productName());
        self::assertSame('100.00', (string) $item->unitPrice());
        self::assertSame('200.00', (string) $item->subtotal());
    }

    public function test_rn_12_the_total_is_the_sum_of_the_lines(): void
    {
        $sale = $this->makeSale();
        $sale->addItem($this->makeProduct(price: '100.00'), Quantity::of(2), 'Ferretería');
        $sale->addItem($this->makeProduct(price: '50.00'), Quantity::of(3), 'Ferretería');

        self::assertSame('350.00', (string) $sale->total());
    }

    public function test_rn_01_selling_more_than_stock_fails_without_touching_the_stock(): void
    {
        $sale = $this->makeSale();
        $product = $this->makeProduct(stock: 1);

        try {
            $sale->addItem($product, Quantity::of(2), 'Ferretería');
            self::fail('Se esperaba InsufficientStockException');
        } catch (InsufficientStockException) {
        }

        self::assertSame(1, $product->stock());
        self::assertSame([], $sale->items());
    }
}
