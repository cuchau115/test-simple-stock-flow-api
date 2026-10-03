<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidRoleException;

/**
 * Closed set of two roles (RN-11). The comparison is case sensitive: "Admin"
 * is not "admin", exactly as the engine column declares with ascii_bin.
 */
enum Role: string
{
    case Admin = 'admin';
    case Seller = 'seller';

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw InvalidRoleException::notAllowed($value);
    }
}
