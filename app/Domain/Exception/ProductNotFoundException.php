<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class ProductNotFoundException extends BusinessRuleViolation
{
    public static function withId(string $id): self
    {
        return new self(sprintf('El producto %s no existe', $id));
    }
}
