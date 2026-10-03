<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidRoleException extends BusinessRuleViolation
{
    public static function notAllowed(string $value): self
    {
        return new self(sprintf('El rol "%s" no es válido', $value));
    }

    public static function missing(): self
    {
        return new self('El rol es obligatorio');
    }
}
