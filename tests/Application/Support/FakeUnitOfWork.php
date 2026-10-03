<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Ports\Outbound\UnitOfWork;

final class FakeUnitOfWork implements UnitOfWork
{
    public int $runs = 0;

    public function run(callable $operation): mixed
    {
        $this->runs++;

        return $operation();
    }
}
