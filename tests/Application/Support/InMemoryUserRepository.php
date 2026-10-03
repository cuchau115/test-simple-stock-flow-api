<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Model\User;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<string, User> */
    private array $users = [];

    public function findByUsername(Username $username): ?User
    {
        return $this->users[$username->value()] ?? null;
    }

    public function findById(UserId $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->id()->equals($id)) {
                return $user;
            }
        }

        return null;
    }

    public function existsByUsername(Username $username): bool
    {
        return isset($this->users[$username->value()]);
    }

    public function add(User $user): void
    {
        $this->users[$user->username()->value()] = $user;
    }
}
