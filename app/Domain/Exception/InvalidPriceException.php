<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidPriceException extends BusinessRuleViolation
{
    public static function notPositive(): self
    {
        return new self('El precio debe ser mayor que cero');
    }

    public static function negativeAmount(): self
    {
        return new self('El importe no puede ser negativo');
    }

    public static function notANumber(string $value): self
    {
        return new self(sprintf('El importe "%s" no es un número válido', $value));
    }
}
