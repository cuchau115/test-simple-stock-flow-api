<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

interface Authenticate
{
    public function login(string $username, string $password): AuthResult;

    public function register(string $username, string $password, string $role): string;
}
