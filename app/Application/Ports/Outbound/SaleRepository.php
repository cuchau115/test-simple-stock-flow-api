<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Application\Model\DateRange;
use App\Application\Model\Page;
use App\Application\Model\PageRequest;
use App\Domain\Model\Sale;
use App\Domain\ValueObject\SaleId;

/**
 * Read and append only. It has exactly these four methods and none that update
 * or delete, because a registered sale is immutable (RN-07).
 */
interface SaleRepository
{
    public function find(SaleId $id): ?Sale;

    public function search(DateRange $range, PageRequest $request): Page;

    /**
     * @return list<Sale>
     */
    public function listByRange(DateRange $range): array;

    public function add(Sale $sale): void;
}
