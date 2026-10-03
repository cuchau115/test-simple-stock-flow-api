<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Exception\InvalidImageException;
use App\Application\Exception\MissingCategoryException;
use App\Application\Exception\ResourceNotFoundException;
use App\Application\Model\Page;
use App\Application\Model\PageRequest;
use App\Application\Ports\Inbound\CategoryView;
use App\Application\Ports\Inbound\ManageProducts;
use App\Application\Ports\Inbound\PagedResult;
use App\Application\Ports\Inbound\ProductView;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\FileStorage;
use App\Application\Ports\Outbound\ProductRepository;
use App\Domain\Exception\InvalidStockException;
use App\Domain\Exception\UnknownCategoryException;
use App\Domain\Model\Category;
use App\Domain\Model\Product;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;

/**
 * Single writer of the catalogue aggregate (RN-08).
 *
 * Two rules that are easy to miss:
 * - The category must exist before the product is built; the missing-category
 *   message wins over a blank name (api-contract.md, E-04/E-05).
 * - Deleting clears the image key first and only then removes the binary, so the
 *   database confirms the detach before the object disappears (D-05).
 */
final class ProductCatalogService implements ManageProducts
{
    private const array ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    private const int MAX_IMAGE_BYTES = 5 * 1024 * 1024;

    private const string NIL_UUID = '00000000-0000-0000-0000-000000000000';

    public function __construct(
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly FileStorage $files,
    ) {
    }

    public function create(string $name, Money $price, int $stock, string $categoryId): string
    {
        $this->assertCategoryExists($categoryId);

        $product = Product::create(
            ProductId::generate(),
            $name,
            $price,
            $stock,
            CategoryId::of($categoryId),
        );

        $this->products->save($product);

        return $product->id()->value();
    }

    public function update(string $productId, string $name, Money $price, int $stock, string $categoryId): void
    {
        $product = $this->findOrFail($productId);

        $this->assertCategoryExists($categoryId);

        $product->rename($name);
        $product->changePrice($price);
        $product->changeCategory(CategoryId::of($categoryId));
        $this->setStock($product, $stock);

        $this->products->save($product);
    }

    public function delete(string $productId): void
    {
        $product = $this->findOrFail($productId);

        $imageKey = $product->imageKey();

        $product->attachImage(null);
        $this->products->save($product);

        $this->products->delete(ProductId::of($productId));

        if ($imageKey !== null) {
            $this->files->delete($imageKey);
        }
    }

    public function get(string $productId): ProductView
    {
        return $this->toView($this->findOrFail($productId));
    }

    public function list(?string $search, ?string $categoryId, PageRequest $request): PagedResult
    {
        $category = $categoryId === null ? null : CategoryId::of($categoryId);

        $page = $this->products->search($search, $category, $request);
        $names = $this->categoryNames($page);

        $items = array_map(
            fn (Product $product): ProductView => new ProductView(
                $product->id()->value(),
                $product->name(),
                $product->price(),
                $product->stock(),
                $product->categoryId()->value(),
                $names[$product->categoryId()->value()] ?? '',
                $product->imageKey() === null ? null : $this->files->url($product->imageKey()),
            ),
            array_values($page->items()),
        );

        return PagedResult::of($items, $request, $page->total());
    }

    public function attachImage(string $productId, string $contentType, string $binary): string
    {
        $product = $this->findOrFail($productId);

        if (! in_array($contentType, self::ALLOWED_IMAGE_TYPES, true)) {
            throw InvalidImageException::notAllowedType($contentType);
        }

        if (strlen($binary) > self::MAX_IMAGE_BYTES) {
            throw InvalidImageException::tooLarge();
        }

        $key = $this->files->store($contentType, $binary);

        $product->attachImage($key);
        $this->products->save($product);

        return $this->files->url($key);
    }

    public function listCategories(): array
    {
        return array_map(
            static fn (Category $category): CategoryView => new CategoryView(
                $category->id()->value(),
                $category->name(),
            ),
            $this->categories->listAll(),
        );
    }

    private function assertCategoryExists(string $categoryId): void
    {
        $id = CategoryId::of($categoryId);

        if ($id->value() === self::NIL_UUID) {
            throw MissingCategoryException::required();
        }

        if ($this->categories->find($id) === null) {
            throw UnknownCategoryException::withId($categoryId);
        }
    }

    private function setStock(Product $product, int $stock): void
    {
        if ($stock < 0) {
            throw InvalidStockException::negativeInitial();
        }

        $current = $product->stock();

        if ($stock > $current) {
            $product->restock($stock - $current);
        } elseif ($stock < $current) {
            $product->withdraw(Quantity::of($current - $stock));
        }
    }

    /**
     * @return array<string, string>
     */
    private function categoryNames(Page $page): array
    {
        $ids = [];

        foreach ($page->items() as $product) {
            if ($product instanceof Product) {
                $ids[$product->categoryId()->value()] = $product->categoryId();
            }
        }

        $names = [];

        foreach ($this->categories->findManyByIds(array_values($ids)) as $category) {
            $names[$category->id()->value()] = $category->name();
        }

        return $names;
    }

    private function findOrFail(string $productId): Product
    {
        $product = $this->products->find(ProductId::of($productId));

        if ($product === null) {
            throw ResourceNotFoundException::product($productId);
        }

        return $product;
    }

    private function toView(Product $product): ProductView
    {
        $category = $this->categories->find($product->categoryId());
        $imageKey = $product->imageKey();

        return new ProductView(
            $product->id()->value(),
            $product->name(),
            $product->price(),
            $product->stock(),
            $product->categoryId()->value(),
            $category?->name() ?? '',
            $imageKey === null ? null : $this->files->url($imageKey),
        );
    }
}
