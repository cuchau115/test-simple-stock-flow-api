<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class DuplicateUsernameException extends BusinessRuleViolation
{
    public static function withUsername(string $username): self
    {
        return new self(sprintf('El nombre de usuario "%s" ya está en uso', $username));
    }
}
