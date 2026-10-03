<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidPasswordHashException extends BusinessRuleViolation
{
    public static function blank(): self
    {
        return new self('La huella de la contraseña no puede estar vacía');
    }
}
