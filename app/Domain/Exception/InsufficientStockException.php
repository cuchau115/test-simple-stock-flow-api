<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InsufficientStockException extends BusinessRuleViolation
{
    public static function forProduct(string $productName, int $available, int $requested): self
    {
        return new self(sprintf(
            "Stock insuficiente para '%s': disponible %d, solicitado %d.",
            $productName,
            $available,
            $requested,
        ));
    }
}
