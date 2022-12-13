<?php

declare(strict_types=1);

namespace App\Exception;

use AnzuSystems\CoreDamBundle\Exception\ValidationException as BaseValidationException;

final class ValidationException extends BaseValidationException
{
    public const ERROR_COMPROMISED_PASSWORD = 'error_compromised_password';
}
