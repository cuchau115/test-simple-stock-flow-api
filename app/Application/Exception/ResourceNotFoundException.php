<?php

declare(strict_types=1);

namespace App\Application\Exception;

use RuntimeException;

/**
 * The addressed resource does not exist.
 *
 * The contract answers 404 with an empty body, so the message never reaches the
 * client; it exists for the logs. It is not a business rule violation: a missing
 * resource is not a bad request.
 */
final class ResourceNotFoundException extends RuntimeException
{
    public static function product(string $id): self
    {
        return new self(sprintf('El producto %s no existe.', $id));
    }

    public static function sale(string $id): self
    {
        return new self(sprintf('La venta %s no existe.', $id));
    }
}
