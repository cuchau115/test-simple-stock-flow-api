<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidNameException extends BusinessRuleViolation
{
    public static function product(): self
    {
        return new self('El nombre del producto es obligatorio.');
    }

    public static function category(): self
    {
        return new self('El nombre de la categoría es obligatorio.');
    }
}
