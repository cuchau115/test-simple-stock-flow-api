<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidQuantityException extends BusinessRuleViolation
{
    public static function notPositive(): self
    {
        return new self('La cantidad debe ser mayor que cero');
    }

    public static function notAnInteger(string $value): self
    {
        return new self(sprintf('La cantidad "%s" no es un número entero válido', $value));
    }
}
