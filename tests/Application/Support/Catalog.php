<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Domain\Model\Category;
use App\Domain\Model\Product;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;

/**
 * Fixed, valid identifiers and builders shared by the application tests.
 */
final class Catalog
{
    public const string CATEGORY_ID = '00000000-0000-4000-8000-0000000000c1';

    public const string OTHER_CATEGORY_ID = '00000000-0000-4000-8000-0000000000c2';

    public const string UNKNOWN_CATEGORY_ID = '00000000-0000-4000-8000-0000000000ff';

    public const string PRODUCT_ID = '00000000-0000-4000-8000-0000000000a1';

    public const string OTHER_PRODUCT_ID = '00000000-0000-4000-8000-0000000000a2';

    public static function category(
        string $id = self::CATEGORY_ID,
        string $name = 'Ferretería',
    ): Category {
        return Category::of(CategoryId::of($id), $name);
    }

    public static function product(
        string $id = self::PRODUCT_ID,
        string $name = 'Tornillo',
        string $price = '100.00',
        int $stock = 10,
        string $categoryId = self::CATEGORY_ID,
    ): Product {
        return Product::create(
            ProductId::of($id),
            $name,
            Money::of($price),
            $stock,
            CategoryId::of($categoryId),
        );
    }
}
