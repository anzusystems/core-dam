<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class PermissionValid extends Constraint
{
    public string $messageSentNotAll = 'permission_not_sent_all';
    public string $messageIncorrectValue = 'permission_incorrect_value';

    public function __construct(
        public bool $requireAll = true,
        array $options = null,
        array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
