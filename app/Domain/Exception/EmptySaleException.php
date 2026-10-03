<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class EmptySaleException extends BusinessRuleViolation
{
    public static function cannotConfirm(): self
    {
        return new self('Una venta debe tener al menos una línea');
    }
}
