<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class UnknownCategoryException extends BusinessRuleViolation
{
    public static function withId(string $id): self
    {
        return new self(sprintf('La categoría %s no existe.', $id));
    }
}
