<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidStockException extends BusinessRuleViolation
{
    public static function negative(): self
    {
        return new self('El stock no puede ser negativo');
    }
}
