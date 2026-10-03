<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidCredentialsException extends BusinessRuleViolation
{
    /**
     * The message never tells apart an unknown user from a wrong password
     * (Article X: the declared state is the real state).
     */
    public static function rejected(): self
    {
        return new self('Usuario o contraseña incorrectos');
    }
}
