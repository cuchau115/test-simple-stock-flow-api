<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

/**
 * Base of every business rule violation.
 *
 * The message is written in Spanish on purpose: it travels untouched to the
 * 422 response and is read by the person using the system (Article XI).
 */
abstract class BusinessRuleViolation extends RuntimeException
{
}
