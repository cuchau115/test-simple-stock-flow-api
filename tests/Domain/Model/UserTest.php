<?php

declare(strict_types=1);

namespace Tests\Domain\Model;

use App\Domain\Exception\InvalidPasswordHashException;
use App\Domain\Exception\InvalidRoleException;
use App\Domain\Model\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_rn_10_the_username_is_normalized(): void
    {
        $user = User::register(UserId::generate(), '  Ana  ', 'hash', Role::Seller);

        self::assertSame('ana', $user->username()->value());
    }

    public function test_rn_11_both_valid_roles_are_accepted(): void
    {
        $admin = User::register(UserId::generate(), 'ana', 'hash', Role::Admin);
        $seller = User::register(UserId::generate(), 'bob', 'hash', 'seller');

        self::assertSame(Role::Admin, $admin->role());
        self::assertSame(Role::Seller, $seller->role());
    }

    public function test_rn_11_a_role_outside_the_set_is_rejected(): void
    {
        $this->expectException(InvalidRoleException::class);

        User::register(UserId::generate(), 'ana', 'hash', 'root');
    }

    public function test_rn_11_a_blank_role_is_rejected(): void
    {
        $this->expectException(InvalidRoleException::class);

        User::register(UserId::generate(), 'ana', 'hash', '');
    }

    public function test_a_blank_password_hash_is_rejected(): void
    {
        $this->expectException(InvalidPasswordHashException::class);

        User::register(UserId::generate(), 'ana', '   ', Role::Seller);
    }
}
