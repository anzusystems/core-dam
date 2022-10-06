<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Exception\ValidationException;
use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
final class UniqueEntityDto extends Constraint
{
    public string $message = ValidationException::ERROR_FIELD_UNIQUE;

    public function __construct(
        public readonly string $entity,
        public readonly array $fields,
        mixed $options = null,
        array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
