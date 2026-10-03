<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

/**
 * The only place that can still express "begin, commit, roll back" without
 * leaking a persistence type across a port.
 *
 * The callable shape makes forgetting the commit, or leaving a transaction
 * open, impossible.
 */
interface UnitOfWork
{
    public function run(callable $operation): mixed;
}
