<?php

declare(strict_types=1);

namespace Tests\Domain\Model;

use App\Domain\Exception\InvalidNameException;
use App\Domain\Model\Category;
use App\Domain\ValueObject\CategoryId;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    private const string CATEGORY_ID = '00000000-0000-4000-8000-0000000000c1';

    public function test_the_name_is_trimmed(): void
    {
        $category = Category::of(CategoryId::of(self::CATEGORY_ID), '  Herramientas  ');

        self::assertSame('Herramientas', $category->name());
    }

    public function test_a_blank_name_is_rejected(): void
    {
        $this->expectException(InvalidNameException::class);

        Category::of(CategoryId::of(self::CATEGORY_ID), '   ');
    }
}
