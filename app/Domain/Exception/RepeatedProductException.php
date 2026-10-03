<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class RepeatedProductException extends BusinessRuleViolation
{
    public static function inSale(): self
    {
        return new self('La venta tiene productos repetidos.');
    }
}
