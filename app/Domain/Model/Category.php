<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\InvalidNameException;
use App\Domain\ValueObject\CategoryId;

/**
 * Reference entity, read only in practice (D-10). The five rows are seeded by
 * a migration; no port creates, renames or deletes them.
 */
final class Category
{
    private function __construct(
        private readonly CategoryId $id,
        private string $name,
    ) {
    }

    public static function of(CategoryId $id, string $name): self
    {
        $category = new self($id, '');
        $category->rename($name);

        return $category;
    }

    public function rename(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            throw InvalidNameException::category();
        }

        $this->name = $name;
    }

    public function id(): CategoryId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }
}
