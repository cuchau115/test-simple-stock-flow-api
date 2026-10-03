<?php

declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\Exception\ConcurrencyConflict;
use App\Application\Ports\Inbound\AuthenticatedUser;
use App\Application\Ports\Inbound\PlaceSaleCommand;
use App\Application\Ports\Inbound\PlaceSaleLine;
use App\Application\UseCase\PlaceSaleService;
use App\Domain\Exception\EmptySaleException;
use App\Domain\Exception\InsufficientStockException;
use App\Domain\Exception\InvalidQuantityException;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Exception\RepeatedProductException;
use App\Domain\ValueObject\ProductId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Application\Support\Catalog;
use Tests\Application\Support\FakeClock;
use Tests\Application\Support\FakeUnitOfWork;
use Tests\Application\Support\InMemoryCategoryRepository;
use Tests\Application\Support\InMemoryProductRepository;
use Tests\Application\Support\InMemorySaleRepository;

final class PlaceSaleServiceTest extends TestCase
{
    private const string SELLER_ID = '00000000-0000-4000-8000-0000000000b1';

    private function seller(): AuthenticatedUser
    {
        return new AuthenticatedUser(self::SELLER_ID, 'Vendedor', 'seller');
    }

    private function command(PlaceSaleLine ...$lines): PlaceSaleCommand
    {
        return new PlaceSaleCommand(array_values($lines));
    }

    private function service(
        InMemoryProductRepository $products,
        InMemorySaleRepository $sales,
        FakeUnitOfWork $unitOfWork = new FakeUnitOfWork(),
    ): PlaceSaleService {
        return new PlaceSaleService(
            $products,
            new InMemoryCategoryRepository(Catalog::category()),
            $sales,
            new FakeClock(new DateTimeImmutable('2026-10-03T12:00:00+00:00')),
            $unitOfWork,
        );
    }

    public function test_it_registers_the_sale_and_withdraws_the_stock(): void
    {
        $products = new InMemoryProductRepository(Catalog::product(stock: 10));
        $sales = new InMemorySaleRepository();
        $unitOfWork = new FakeUnitOfWork();

        $saleId = $this->service($products, $sales, $unitOfWork)->place(
            $this->command(new PlaceSaleLine(Catalog::PRODUCT_ID, 2)),
            $this->seller(),
        );

        $sale = $sales->find($saleId);
        self::assertNotNull($sale);
        self::assertSame('vendedor', $sale->soldByUsername()->value());
        self::assertSame('200.00', (string) $sale->total());
        self::assertSame(8, $products->find(ProductId::of(Catalog::PRODUCT_ID))?->stock());
        self::assertSame(1, $unitOfWork->runs);
    }

    public function test_a_sale_without_lines_is_rejected(): void
    {
        $this->expectException(EmptySaleException::class);

        $this->service(new InMemoryProductRepository(), new InMemorySaleRepository())
            ->place(new PlaceSaleCommand([]), $this->seller());
    }

    public function test_a_repeated_product_is_rejected_before_anything_else(): void
    {
        $this->expectException(RepeatedProductException::class);

        $this->service(
            new InMemoryProductRepository(Catalog::product(stock: 10)),
            new InMemorySaleRepository(),
        )->place(
            $this->command(
                new PlaceSaleLine(Catalog::PRODUCT_ID, 1),
                new PlaceSaleLine(Catalog::PRODUCT_ID, 1),
            ),
            $this->seller(),
        );
    }

    public function test_an_unknown_product_is_reported_as_not_found(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $this->service(new InMemoryProductRepository(), new InMemorySaleRepository())
            ->place(
                $this->command(new PlaceSaleLine(Catalog::OTHER_PRODUCT_ID, 1)),
                $this->seller(),
            );
    }

    public function test_an_unknown_product_wins_over_a_zero_quantity(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $this->service(new InMemoryProductRepository(), new InMemorySaleRepository())
            ->place(
                $this->command(new PlaceSaleLine(Catalog::OTHER_PRODUCT_ID, 0)),
                $this->seller(),
            );
    }

    public function test_a_non_positive_quantity_is_rejected(): void
    {
        $this->expectException(InvalidQuantityException::class);

        $this->service(
            new InMemoryProductRepository(Catalog::product(stock: 10)),
            new InMemorySaleRepository(),
        )->place(
            $this->command(new PlaceSaleLine(Catalog::PRODUCT_ID, 0)),
            $this->seller(),
        );
    }

    public function test_selling_more_than_the_stock_is_rejected(): void
    {
        $this->expectException(InsufficientStockException::class);

        $this->service(
            new InMemoryProductRepository(Catalog::product(stock: 1)),
            new InMemorySaleRepository(),
        )->place(
            $this->command(new PlaceSaleLine(Catalog::PRODUCT_ID, 2)),
            $this->seller(),
        );
    }

    public function test_it_retries_the_attempt_after_a_concurrency_conflict(): void
    {
        $products = new InMemoryProductRepository(Catalog::product(stock: 10));
        $products->failSaves = 1;
        $sales = new InMemorySaleRepository();
        $unitOfWork = new FakeUnitOfWork();

        $saleId = $this->service($products, $sales, $unitOfWork)->place(
            $this->command(new PlaceSaleLine(Catalog::PRODUCT_ID, 2)),
            $this->seller(),
        );

        self::assertNotNull($sales->find($saleId));
        self::assertSame(8, $products->find(ProductId::of(Catalog::PRODUCT_ID))?->stock());
        self::assertSame(2, $unitOfWork->runs);
    }

    public function test_it_gives_up_after_three_lost_attempts(): void
    {
        $products = new InMemoryProductRepository(Catalog::product(stock: 10));
        $products->failSaves = 3;
        $unitOfWork = new FakeUnitOfWork();

        try {
            $this->service($products, new InMemorySaleRepository(), $unitOfWork)->place(
                $this->command(new PlaceSaleLine(Catalog::PRODUCT_ID, 2)),
                $this->seller(),
            );
            self::fail('Se esperaba ConcurrencyConflict');
        } catch (ConcurrencyConflict) {
        }

        self::assertSame(3, $unitOfWork->runs);
    }
}
