<?php

declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\Exception\InvalidImageException;
use App\Application\Exception\MissingCategoryException;
use App\Application\Exception\ResourceNotFoundException;
use App\Application\Model\PageRequest;
use App\Application\UseCase\ProductCatalogService;
use App\Domain\Exception\InvalidNameException;
use App\Domain\Exception\InvalidStockException;
use App\Domain\Exception\UnknownCategoryException;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use PHPUnit\Framework\TestCase;
use Tests\Application\Support\Catalog;
use Tests\Application\Support\FakeFileStorage;
use Tests\Application\Support\InMemoryCategoryRepository;
use Tests\Application\Support\InMemoryProductRepository;

final class ProductCatalogServiceTest extends TestCase
{
    private function categories(): InMemoryCategoryRepository
    {
        return new InMemoryCategoryRepository(
            Catalog::category(),
            Catalog::category(Catalog::OTHER_CATEGORY_ID, 'Herramientas'),
        );
    }

    private function service(
        InMemoryProductRepository $products,
        ?InMemoryCategoryRepository $categories = null,
        ?FakeFileStorage $files = null,
    ): ProductCatalogService {
        return new ProductCatalogService(
            $products,
            $categories ?? $this->categories(),
            $files ?? new FakeFileStorage(),
        );
    }

    public function test_create_persists_a_product_in_a_known_category(): void
    {
        $products = new InMemoryProductRepository();

        $id = $this->service($products)->create('Tornillo', Money::of('100.00'), 5, Catalog::CATEGORY_ID);

        $stored = $products->find(ProductId::of($id));
        self::assertNotNull($stored);
        self::assertSame('Tornillo', $stored->name());
        self::assertSame(5, $stored->stock());
        self::assertSame(Catalog::CATEGORY_ID, $stored->categoryId()->value());
    }

    public function test_create_with_an_unknown_category_is_rejected(): void
    {
        $this->expectException(UnknownCategoryException::class);

        $this->service(new InMemoryProductRepository())
            ->create('Tornillo', Money::of('100.00'), 5, Catalog::UNKNOWN_CATEGORY_ID);
    }

    public function test_the_unknown_category_wins_over_a_blank_name(): void
    {
        $this->expectException(UnknownCategoryException::class);

        $this->service(new InMemoryProductRepository())
            ->create('   ', Money::of('100.00'), 5, Catalog::UNKNOWN_CATEGORY_ID);
    }

    public function test_a_blank_name_with_a_known_category_is_rejected(): void
    {
        $this->expectException(InvalidNameException::class);

        $this->service(new InMemoryProductRepository())
            ->create('   ', Money::of('100.00'), 5, Catalog::CATEGORY_ID);
    }

    public function test_the_nil_category_is_reported_as_required(): void
    {
        $this->expectException(MissingCategoryException::class);

        $this->service(new InMemoryProductRepository())
            ->create('Tornillo', Money::of('100.00'), 5, '00000000-0000-0000-0000-000000000000');
    }

    public function test_update_with_a_negative_stock_is_rejected(): void
    {
        $products = new InMemoryProductRepository(Catalog::product(stock: 10));

        $this->expectException(InvalidStockException::class);

        $this->service($products)->update(
            Catalog::PRODUCT_ID,
            'Tornillo',
            Money::of('100.00'),
            -1,
            Catalog::CATEGORY_ID,
        );
    }

    public function test_get_returns_the_view_with_its_category_name(): void
    {
        $products = new InMemoryProductRepository(Catalog::product());

        $view = $this->service($products)->get(Catalog::PRODUCT_ID);

        self::assertSame(Catalog::PRODUCT_ID, $view->id);
        self::assertSame('Tornillo', $view->name);
        self::assertSame('Ferretería', $view->categoryName);
        self::assertSame('COP', $view->currency());
        self::assertNull($view->imageUrl);
    }

    public function test_get_of_a_missing_product_raises_not_found(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->service(new InMemoryProductRepository())->get(Catalog::PRODUCT_ID);
    }

    public function test_update_changes_the_attributes_and_grows_the_stock(): void
    {
        $products = new InMemoryProductRepository(Catalog::product(stock: 4));

        $this->service($products)->update(
            Catalog::PRODUCT_ID,
            'Tornillo reforzado',
            Money::of('250.00'),
            10,
            Catalog::OTHER_CATEGORY_ID,
        );

        $stored = $products->find(ProductId::of(Catalog::PRODUCT_ID));
        self::assertNotNull($stored);
        self::assertSame('Tornillo reforzado', $stored->name());
        self::assertSame('250.00', (string) $stored->price());
        self::assertSame(10, $stored->stock());
        self::assertSame(Catalog::OTHER_CATEGORY_ID, $stored->categoryId()->value());
    }

    public function test_update_shrinks_the_stock(): void
    {
        $products = new InMemoryProductRepository(Catalog::product(stock: 10));

        $this->service($products)->update(
            Catalog::PRODUCT_ID,
            'Tornillo',
            Money::of('100.00'),
            3,
            Catalog::CATEGORY_ID,
        );

        self::assertSame(3, $products->find(ProductId::of(Catalog::PRODUCT_ID))?->stock());
    }

    public function test_update_of_a_missing_product_raises_not_found(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->service(new InMemoryProductRepository())->update(
            Catalog::PRODUCT_ID,
            'Tornillo',
            Money::of('100.00'),
            3,
            Catalog::CATEGORY_ID,
        );
    }

    public function test_update_with_an_unknown_category_is_rejected(): void
    {
        $products = new InMemoryProductRepository(Catalog::product());

        $this->expectException(UnknownCategoryException::class);

        $this->service($products)->update(
            Catalog::PRODUCT_ID,
            'Tornillo',
            Money::of('100.00'),
            3,
            Catalog::UNKNOWN_CATEGORY_ID,
        );
    }

    public function test_attach_image_stores_the_binary_and_links_it(): void
    {
        $products = new InMemoryProductRepository(Catalog::product());
        $files = new FakeFileStorage();
        $service = $this->service($products, null, $files);

        $url = $service->attachImage(Catalog::PRODUCT_ID, 'image/png', 'PNGDATA');

        $key = substr($url, strlen('https://cdn.test/'));
        self::assertTrue($files->has($key));
        self::assertSame($key, $products->find(ProductId::of(Catalog::PRODUCT_ID))?->imageKey());
        self::assertSame($url, $service->get(Catalog::PRODUCT_ID)->imageUrl);
    }

    public function test_attach_image_with_a_foreign_type_is_rejected(): void
    {
        $this->expectException(InvalidImageException::class);

        $this->service(new InMemoryProductRepository(Catalog::product()))
            ->attachImage(Catalog::PRODUCT_ID, 'application/pdf', 'PDF');
    }

    public function test_attach_image_bigger_than_five_megabytes_is_rejected(): void
    {
        $this->expectException(InvalidImageException::class);

        $this->service(new InMemoryProductRepository(Catalog::product()))
            ->attachImage(Catalog::PRODUCT_ID, 'image/png', str_repeat('x', 5 * 1024 * 1024 + 1));
    }

    public function test_delete_clears_the_image_before_removing_the_binary(): void
    {
        $products = new InMemoryProductRepository(Catalog::product());
        $files = new FakeFileStorage();
        $service = $this->service($products, null, $files);
        $url = $service->attachImage(Catalog::PRODUCT_ID, 'image/png', 'PNGDATA');
        $key = substr($url, strlen('https://cdn.test/'));

        $service->delete(Catalog::PRODUCT_ID);

        self::assertNull($products->find(ProductId::of(Catalog::PRODUCT_ID))?->imageKey());
        self::assertTrue($products->isDeleted(ProductId::of(Catalog::PRODUCT_ID)));
        self::assertContains($key, $files->deleted);
        self::assertFalse($files->has($key));
    }

    public function test_delete_of_a_missing_product_raises_not_found(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->service(new InMemoryProductRepository())->delete(Catalog::PRODUCT_ID);
    }

    public function test_list_filters_by_search_and_pages(): void
    {
        $products = new InMemoryProductRepository(
            Catalog::product(id: Catalog::PRODUCT_ID, name: 'Tornillo'),
            Catalog::product(id: Catalog::OTHER_PRODUCT_ID, name: 'Martillo'),
        );

        $page = $this->service($products)->list('torn', null, PageRequest::of(1, 1));

        self::assertSame(1, $page->total);
        self::assertCount(1, $page->items);
        self::assertSame('Tornillo', $page->items[0]->name);
        self::assertSame('Ferretería', $page->items[0]->categoryName);
    }

    public function test_list_returns_every_product_when_there_is_no_filter(): void
    {
        $products = new InMemoryProductRepository(
            Catalog::product(id: Catalog::PRODUCT_ID),
            Catalog::product(id: Catalog::OTHER_PRODUCT_ID),
        );

        $page = $this->service($products)->list(null, null, PageRequest::of());

        self::assertSame(2, $page->total);
        self::assertCount(2, $page->items);
    }

    public function test_list_categories_is_ordered_by_name(): void
    {
        $categories = $this->service(new InMemoryProductRepository())->listCategories();

        self::assertSame('Ferretería', $categories[0]->name);
        self::assertSame('Herramientas', $categories[1]->name);
    }
}
