<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Exception\AdminRegistrationNotAllowed;
use App\Application\Ports\Inbound\Authenticate;
use App\Application\Ports\Inbound\AuthResult;
use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\TokenGenerator;
use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Exception\DuplicateUsernameException;
use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Model\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;

/**
 * The only writer of the identity aggregate.
 *
 * It never sees a plain password leave: the hash is produced by the outbound
 * `PasswordHasher` port and only the hash reaches `User` (D-09). Registration is
 * sellers only; administrators are provisioned by the deployment (DP-04).
 */
final class AuthenticationService implements Authenticate
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher,
        private readonly TokenGenerator $tokens,
    ) {
    }

    public function login(string $username, string $password): AuthResult
    {
        $name = Username::of($username);
        $user = $this->users->findByUsername($name);

        if ($user === null || ! $this->hasher->verify($password, $user->passwordHash())) {
            throw InvalidCredentialsException::rejected();
        }

        $token = $this->tokens->generate($user);

        return new AuthResult(
            $token->value,
            $token->expiresAt,
            $user->username()->value(),
            $user->role()->value,
        );
    }

    public function register(string $username, string $password, string $role): string
    {
        $name = Username::of($username);

        if ($role === Role::Admin->value) {
            throw AdminRegistrationNotAllowed::becauseAdminsAreProvisioned();
        }

        if ($this->users->existsByUsername($name)) {
            throw DuplicateUsernameException::withUsername($name->value());
        }

        $user = User::register(
            UserId::generate(),
            $name->value(),
            $this->hasher->hash($password),
            Role::fromString($role),
        );

        $this->users->add($user);

        return $user->id()->value();
    }
}
