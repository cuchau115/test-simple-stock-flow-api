<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Exception\ConcurrencyConflict;
use App\Application\Ports\Inbound\AuthenticatedUser;
use App\Application\Ports\Inbound\PlaceSale;
use App\Application\Ports\Inbound\PlaceSaleCommand;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\Clock;
use App\Application\Ports\Outbound\ProductRepository;
use App\Application\Ports\Outbound\SaleRepository;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Domain\Exception\EmptySaleException;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Exception\RepeatedProductException;
use App\Domain\Exception\UnknownCategoryException;
use App\Domain\Model\Product;
use App\Domain\Model\Sale;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\SaleId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;

/**
 * The only writer of the sales aggregate.
 *
 * The whole attempt, price reading included, runs inside `UnitOfWork::run`; on a
 * lost optimistic check the unit of work rolls back and the attempt is repeated
 * up to three times, reloading the products each time (RN-09, CA-05.5).
 *
 * Validation order is contractual (api-contract.md, E-10): non-empty lines,
 * no repeated product, product exists, quantity positive, stock enough.
 */
final class PlaceSaleService implements PlaceSale
{
    private const int MAX_ATTEMPTS = 3;

    public function __construct(
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly SaleRepository $sales,
        private readonly Clock $clock,
        private readonly UnitOfWork $unitOfWork,
    ) {
    }

    public function place(PlaceSaleCommand $command, AuthenticatedUser $soldBy): SaleId
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $this->unitOfWork->run(
                    fn (): SaleId => $this->attempt($command, $soldBy),
                );
            } catch (ConcurrencyConflict $conflict) {
                if ($attempt >= self::MAX_ATTEMPTS) {
                    throw ConcurrencyConflict::afterRetries();
                }
            }
        }
    }

    private function attempt(PlaceSaleCommand $command, AuthenticatedUser $soldBy): SaleId
    {
        $lines = $command->lines;

        if ($lines === []) {
            throw EmptySaleException::cannotConfirm();
        }

        $this->assertNoRepeatedProducts($lines);

        [$byId, $categoryNames] = $this->loadProducts($lines);

        $sale = Sale::create(
            SaleId::generate(),
            $this->clock->now(),
            Username::of($soldBy->username),
            UserId::of($soldBy->id),
        );

        foreach ($lines as $line) {
            $product = $byId[ProductId::of($line->productId)->value()] ?? null;

            if ($product === null) {
                throw ProductNotFoundException::withId($line->productId);
            }

            $categoryName = $categoryNames[$product->categoryId()->value()] ?? null;

            if ($categoryName === null) {
                throw UnknownCategoryException::withId($product->categoryId()->value());
            }

            $sale->addItem(
                $product,
                Quantity::of($line->quantity),
                $categoryName,
            );
        }

        $sale->ensureConfirmable();

        foreach ($byId as $product) {
            $this->products->save($product);
        }

        $this->sales->add($sale);

        return $sale->id();
    }

    /**
     * @param  list<\App\Application\Ports\Inbound\PlaceSaleLine>  $lines
     */
    private function assertNoRepeatedProducts(array $lines): void
    {
        $seen = [];

        foreach ($lines as $line) {
            if (isset($seen[$line->productId])) {
                throw RepeatedProductException::inSale();
            }

            $seen[$line->productId] = true;
        }
    }

    /**
     * @param  list<\App\Application\Ports\Inbound\PlaceSaleLine>  $lines
     * @return array{array<string, Product>, array<string, string>}
     */
    private function loadProducts(array $lines): array
    {
        $ids = [];

        foreach ($lines as $line) {
            $ids[] = ProductId::of($line->productId);
        }

        $products = $this->products->findActiveByIds($ids);

        $byId = [];
        $categoryIds = [];

        foreach ($products as $product) {
            $byId[$product->id()->value()] = $product;
            $categoryIds[$product->categoryId()->value()] = $product->categoryId();
        }

        $categoryNames = [];

        foreach ($this->categories->findManyByIds(array_values($categoryIds)) as $category) {
            $categoryNames[$category->id()->value()] = $category->name();
        }

        return [$byId, $categoryNames];
    }
}
