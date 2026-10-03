<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Model\User;

interface TokenGenerator
{
    public function generate(User $user): Token;
}
