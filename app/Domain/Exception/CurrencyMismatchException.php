<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class CurrencyMismatchException extends BusinessRuleViolation
{
    public static function expected(string $expected, string $given): self
    {
        return new self(sprintf('La moneda del importe debe ser %s, no %s.', $expected, $given));
    }
}
