<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Exception\ResourceNotFoundException;
use App\Application\Model\DateRange;
use App\Application\Model\PageRequest;
use App\Application\Ports\Inbound\GetSales;
use App\Application\Ports\Inbound\PagedResult;
use App\Application\Ports\Inbound\SaleItemView;
use App\Application\Ports\Inbound\SaleView;
use App\Application\Ports\Outbound\SaleRepository;
use App\Domain\Model\Sale;
use App\Domain\Model\SaleItem;
use App\Domain\ValueObject\SaleId;

/**
 * Read-only view of the sales aggregate (RN-07). The total is computed from the
 * lines, never read from a column (RN-12).
 */
final class GetSalesService implements GetSales
{
    public function __construct(
        private readonly SaleRepository $sales,
    ) {
    }

    public function get(string $saleId): SaleView
    {
        $sale = $this->sales->find(SaleId::of($saleId));

        if ($sale === null) {
            throw ResourceNotFoundException::sale($saleId);
        }

        return $this->toView($sale);
    }

    public function list(DateRange $range, PageRequest $request): PagedResult
    {
        $page = $this->sales->search($range, $request);

        $items = array_map(
            fn (Sale $sale): SaleView => $this->toView($sale),
            array_values($page->items()),
        );

        return PagedResult::of($items, $request, $page->total());
    }

    private function toView(Sale $sale): SaleView
    {
        $items = array_map(
            static fn (SaleItem $item): SaleItemView => new SaleItemView(
                $item->productId()->value(),
                $item->productName(),
                $item->quantity()->value(),
                $item->unitPrice(),
                $item->subtotal(),
            ),
            $sale->items(),
        );

        return new SaleView(
            $sale->id()->value(),
            $sale->soldAt(),
            $sale->soldByUsername()->value(),
            $sale->total(),
            $items,
        );
    }
}
