<?php

declare(strict_types=1);

namespace App\Application\Exception;

use RuntimeException;

/**
 * Raised by the persistence adapter when the optimistic version check fails and
 * the use case has exhausted its retries. It is not a business rule violation:
 * the contract answers 409, not 422.
 */
final class ConcurrencyConflict extends RuntimeException
{
    public static function afterRetries(): self
    {
        return new self('Otra operación modificó los datos al mismo tiempo. Inténtalo de nuevo.');
    }
}
