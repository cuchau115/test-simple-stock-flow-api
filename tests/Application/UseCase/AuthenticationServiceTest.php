<?php

declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\Exception\AdminRegistrationNotAllowed;
use App\Application\UseCase\AuthenticationService;
use App\Domain\Exception\DuplicateUsernameException;
use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Exception\InvalidRoleException;
use App\Domain\Model\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;
use PHPUnit\Framework\TestCase;
use Tests\Application\Support\FakePasswordHasher;
use Tests\Application\Support\FakeTokenGenerator;
use Tests\Application\Support\InMemoryUserRepository;

final class AuthenticationServiceTest extends TestCase
{
    private function service(InMemoryUserRepository $users): AuthenticationService
    {
        return new AuthenticationService(
            $users,
            new FakePasswordHasher(),
            new FakeTokenGenerator(),
        );
    }

    private function seedUser(string $username, string $plain, Role $role): InMemoryUserRepository
    {
        $users = new InMemoryUserRepository();
        $users->add(User::register(
            UserId::generate(),
            $username,
            'hashed:'.$plain,
            $role,
        ));

        return $users;
    }

    public function test_it_registers_a_seller_and_stores_only_the_hash(): void
    {
        $users = new InMemoryUserRepository();

        $id = $this->service($users)->register('Vendedor', 'secreto', 'seller');

        $stored = $users->findByUsername(Username::of('vendedor'));
        self::assertNotNull($stored);
        self::assertSame($id, $stored->id()->value());
        self::assertSame('hashed:secreto', $stored->passwordHash());
        self::assertSame(Role::Seller, $stored->role());
    }

    public function test_the_username_is_normalized_on_registration(): void
    {
        $users = new InMemoryUserRepository();

        $this->service($users)->register('  VENDEDOR  ', 'secreto', 'seller');

        self::assertNotNull($users->findByUsername(Username::of('vendedor')));
    }

    public function test_registering_an_admin_is_rejected(): void
    {
        $this->expectException(AdminRegistrationNotAllowed::class);

        $this->service(new InMemoryUserRepository())->register('jefe', 'secreto', 'admin');
    }

    public function test_the_admin_message_wins_over_a_duplicate_username(): void
    {
        $users = $this->seedUser('jefe', 'secreto', Role::Admin);

        $this->expectException(AdminRegistrationNotAllowed::class);

        $this->service($users)->register('jefe', 'secreto', 'admin');
    }

    public function test_a_duplicate_username_is_rejected_case_insensitively(): void
    {
        $users = $this->seedUser('vendedor', 'secreto', Role::Seller);

        $this->expectException(DuplicateUsernameException::class);

        $this->service($users)->register('VENDEDOR', 'otra', 'seller');
    }

    public function test_an_unknown_role_is_rejected(): void
    {
        $this->expectException(InvalidRoleException::class);

        $this->service(new InMemoryUserRepository())->register('vendedor', 'secreto', 'manager');
    }

    public function test_it_logs_in_with_valid_credentials(): void
    {
        $users = $this->seedUser('vendedor', 'secreto', Role::Seller);

        $result = $this->service($users)->login('VENDEDOR', 'secreto');

        self::assertSame('token-de-prueba', $result->accessToken);
        self::assertSame('vendedor', $result->username);
        self::assertSame('seller', $result->role);
    }

    public function test_an_unknown_user_and_a_wrong_password_look_the_same(): void
    {
        $service = $this->service($this->seedUser('vendedor', 'secreto', Role::Seller));
        $messages = [];

        try {
            $service->login('noexiste', 'secreto');
        } catch (InvalidCredentialsException $exception) {
            $messages[] = $exception->getMessage();
        }

        try {
            $service->login('vendedor', 'incorrecta');
        } catch (InvalidCredentialsException $exception) {
            $messages[] = $exception->getMessage();
        }

        self::assertCount(2, $messages);
        self::assertSame($messages[0], $messages[1]);
    }
}
