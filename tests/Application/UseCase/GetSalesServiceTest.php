<?php

declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\Exception\ResourceNotFoundException;
use App\Application\Model\DateRange;
use App\Application\Model\PageRequest;
use App\Application\UseCase\GetSalesService;
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
use Tests\Application\Support\Catalog;
use Tests\Application\Support\InMemorySaleRepository;

final class GetSalesServiceTest extends TestCase
{
    private function makeSale(
        string $at,
        string $productId,
        int $quantity,
        string $price,
        string $seller = 'vendedor',
    ): Sale {
        $product = Product::create(
            ProductId::of($productId),
            'Tornillo',
            Money::of($price),
            100,
            CategoryId::of(Catalog::CATEGORY_ID),
        );

        $sale = Sale::create(
            SaleId::generate(),
            new DateTimeImmutable($at),
            Username::of($seller),
            UserId::generate(),
        );

        $sale->addItem($product, Quantity::of($quantity), 'Ferretería');

        return $sale;
    }

    public function test_get_returns_the_sale_with_its_lines_and_total(): void
    {
        $sale = $this->makeSale('2026-10-03T10:00:00+00:00', Catalog::PRODUCT_ID, 2, '100.00');
        $sales = new InMemorySaleRepository();
        $sales->add($sale);

        $view = (new GetSalesService($sales))->get($sale->id()->value());

        self::assertSame($sale->id()->value(), $view->id);
        self::assertSame('vendedor', $view->soldBy);
        self::assertSame('200.00', (string) $view->total);
        self::assertSame('COP', $view->currency());
        self::assertCount(1, $view->items);
        self::assertSame(Catalog::PRODUCT_ID, $view->items[0]->productId);
        self::assertSame(2, $view->items[0]->quantity);
        self::assertSame('100.00', (string) $view->items[0]->unitPrice);
        self::assertSame('200.00', (string) $view->items[0]->subtotal);
    }

    public function test_get_of_a_missing_sale_raises_not_found(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        (new GetSalesService(new InMemorySaleRepository()))->get(Catalog::PRODUCT_ID);
    }

    public function test_list_paginates_and_reports_the_total(): void
    {
        $sales = new InMemorySaleRepository();
        $sales->add($this->makeSale('2026-10-03T10:00:00+00:00', Catalog::PRODUCT_ID, 1, '100.00'));
        $sales->add($this->makeSale('2026-10-03T12:00:00+00:00', Catalog::OTHER_PRODUCT_ID, 1, '100.00'));

        $range = DateRange::of(
            new DateTimeImmutable('2026-10-03T00:00:00+00:00'),
            new DateTimeImmutable('2026-10-04T00:00:00+00:00'),
        );

        $page = (new GetSalesService($sales))->list($range, PageRequest::of(1, 1));

        self::assertSame(2, $page->total);
        self::assertSame(2, $page->totalPages);
        self::assertCount(1, $page->items);
    }

    public function test_list_leaves_out_sales_outside_the_range(): void
    {
        $sales = new InMemorySaleRepository();
        $sales->add($this->makeSale('2026-09-01T10:00:00+00:00', Catalog::PRODUCT_ID, 1, '100.00'));

        $range = DateRange::of(
            new DateTimeImmutable('2026-10-03T00:00:00+00:00'),
            new DateTimeImmutable('2026-10-04T00:00:00+00:00'),
        );

        $page = (new GetSalesService($sales))->list($range, PageRequest::of());

        self::assertSame(0, $page->total);
        self::assertSame([], $page->items);
    }
}
