<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidNameException extends BusinessRuleViolation
{
    public static function blank(string $what): self
    {
        return new self(sprintf('El nombre de %s no puede estar vacío', $what));
    }
}
