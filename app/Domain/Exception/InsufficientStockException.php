<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InsufficientStockException extends BusinessRuleViolation
{
    public static function forRequest(int $requested, int $available): self
    {
        return new self(sprintf(
            'No hay stock suficiente: se pidieron %d y hay %d',
            $requested,
            $available,
        ));
    }
}
