<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Exception\ValidationException;
use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class RtmpPath extends Constraint
{
    public string $message = ValidationException::ERROR_FIELD_INVALID;
}
