<?php

declare(strict_types=1);

namespace App\Application\Exception;

use App\Domain\Exception\BusinessRuleViolation;

/**
 * Sentinel of the contract: the app sends the nil UUID to mean "no category".
 * It is a 422 of §2.1, not a 400, so it is a business rule violation
 * (api-contract.md, E-05/E-06).
 */
final class MissingCategoryException extends BusinessRuleViolation
{
    public static function required(): self
    {
        return new self('La categoría es obligatoria.');
    }
}
