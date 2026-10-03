<?php

declare(strict_types=1);

namespace App\Application\Exception;

use App\Domain\Exception\BusinessRuleViolation;

/**
 * DP-04 made executable: the registration port does not know how to create
 * administrators. Raising it here, before the duplicate check, fixes the
 * contract's order of messages (api-contract.md, E-02).
 */
final class AdminRegistrationNotAllowed extends BusinessRuleViolation
{
    public static function becauseAdminsAreProvisioned(): self
    {
        return new self('Solo se pueden dar de alta vendedores. El administrador lo crea el despliegue.');
    }
}
