<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidUsernameException extends BusinessRuleViolation
{
    public static function blank(): self
    {
        return new self('El nombre de usuario no puede estar vacío');
    }
}
