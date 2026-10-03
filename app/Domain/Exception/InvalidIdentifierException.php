<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidIdentifierException extends BusinessRuleViolation
{
    public static function blank(): self
    {
        return new self('El identificador no puede estar vacío.');
    }

    public static function notAUuid(string $value): self
    {
        return new self(sprintf('El identificador "%s" no es un UUID válido.', $value));
    }
}
