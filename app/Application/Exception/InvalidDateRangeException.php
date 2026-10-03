<?php

declare(strict_types=1);

namespace App\Application\Exception;

use App\Domain\Exception\BusinessRuleViolation;

/**
 * The `from <= sold_at < to` shape is an application rule enforced by
 * `DateRange`, but it is still a business rule: the contract answers 422 with
 * this message, not 409.
 */
final class InvalidDateRangeException extends BusinessRuleViolation
{
    public static function toBeforeFrom(): self
    {
        return new self('La fecha final no puede ser anterior a la inicial.');
    }
}
