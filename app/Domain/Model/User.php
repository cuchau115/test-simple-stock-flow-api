<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\InvalidPasswordHashException;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;

/**
 * Aggregate root of identity.
 *
 * The domain never sees the plain password: the hash arrives already produced
 * by the PasswordHasher port (D-09). The username is normalized to lowercase
 * and trimmed (RN-10) and the role belongs to a closed set (RN-11).
 */
final class User
{
    private function __construct(
        private readonly UserId $id,
        private readonly Username $username,
        private readonly string $passwordHash,
        private readonly Role $role,
    ) {
    }

    public static function register(
        UserId $id,
        string $username,
        string $passwordHash,
        Role|string $role,
    ): self {
        $passwordHash = trim($passwordHash);

        if ($passwordHash === '') {
            throw InvalidPasswordHashException::blank();
        }

        return new self(
            $id,
            Username::of($username),
            $passwordHash,
            $role instanceof Role ? $role : Role::fromString($role),
        );
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function role(): Role
    {
        return $this->role;
    }
}
