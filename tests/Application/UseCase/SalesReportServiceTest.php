<?php

declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\Model\DateRange;
use App\Application\Ports\Outbound\SalesAggregate;
use App\Application\Ports\Outbound\SalesAggregateRow;
use App\Application\UseCase\SalesReportService;
use App\Domain\ValueObject\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Application\Support\Catalog;
use Tests\Application\Support\FakeSalesReportQuery;

final class SalesReportServiceTest extends TestCase
{
    private function range(): DateRange
    {
        return DateRange::of(
            new DateTimeImmutable('2026-10-01T00:00:00+00:00'),
            new DateTimeImmutable('2026-10-04T00:00:00+00:00'),
        );
    }

    public function test_report_reshapes_the_aggregate_rows(): void
    {
        $query = new FakeSalesReportQuery();
        $query->willReturn(new SalesAggregate(
            3,
            Money::of('1200.00'),
            [
                new SalesAggregateRow(
                    Catalog::PRODUCT_ID,
                    'Tornillo',
                    'Ferretería',
                    10,
                    Money::of('1000.00'),
                ),
                new SalesAggregateRow(
                    Catalog::OTHER_PRODUCT_ID,
                    'Martillo',
                    'Herramientas',
                    1,
                    Money::of('200.00'),
                ),
            ],
        ));

        $report = (new SalesReportService($query))->report($this->range());

        self::assertSame(3, $report->salesCount);
        self::assertSame('1200.00', (string) $report->grandTotal);
        self::assertSame('COP', $report->currency());
        self::assertCount(2, $report->rows);
        self::assertSame('Tornillo', $report->rows[0]->productName);
        self::assertSame(10, $report->rows[0]->unitsSold);
        self::assertSame('Herramientas', $report->rows[1]->categoryName);
    }

    public function test_an_empty_aggregate_produces_an_empty_report_with_zero_total(): void
    {
        $report = (new SalesReportService(new FakeSalesReportQuery()))->report($this->range());

        self::assertSame(0, $report->salesCount);
        self::assertSame('0.00', (string) $report->grandTotal);
        self::assertSame([], $report->rows);
    }
}
