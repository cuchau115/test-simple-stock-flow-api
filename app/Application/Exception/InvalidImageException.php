<?php

declare(strict_types=1);

namespace App\Application\Exception;

use App\Domain\Exception\BusinessRuleViolation;

/**
 * Image shape is an application rule, not a domain one: the domain never sees
 * bytes or content types. It still answers 422, never 500 (api-contract.md,
 * E-08), so it extends the business rule base.
 */
final class InvalidImageException extends BusinessRuleViolation
{
    public static function notAllowedType(string $contentType): self
    {
        return new self(sprintf('Tipo de archivo no permitido: %s.', $contentType));
    }

    public static function tooLarge(): self
    {
        return new self('La imagen supera el máximo de 5 MB.');
    }
}
