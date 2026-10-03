<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class RepeatedProductException extends BusinessRuleViolation
{
    public static function inSale(): self
    {
        return new self('Un producto no puede repetirse en la misma venta');
    }
}
