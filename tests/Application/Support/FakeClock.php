<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Ports\Outbound\Clock;
use DateTimeImmutable;

final class FakeClock implements Clock
{
    public function __construct(private DateTimeImmutable $now)
    {
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function set(DateTimeImmutable $now): void
    {
        $this->now = $now;
    }
}
