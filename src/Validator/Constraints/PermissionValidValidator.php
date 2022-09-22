<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Permission\Grants;
use App\Permission\Permissions;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class PermissionValidValidator extends ConstraintValidator
{
    /**
     * @param array $value
     * @param PermissionValid $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint->requireAll) {
            foreach (Permissions::all() as $permission) {
                if (false === array_key_exists($permission, $value)) {
                    $this->context->addViolation($constraint->messageSentNotAll);

                    break;
                }
            }
        }

        /** @var array<string, array> $permissionAllowedValues */
        $permissionAllowedValues = Permissions::permissionAllowedValues();
        foreach ($this->iterateSentPermissions($value) as $sentPermissionName => $sentPermissionValue) {
            if (false === array_key_exists($sentPermissionName, $permissionAllowedValues)) {
                $this->context->addViolation($constraint->messageIncorrectValue);

                return;
            }
            if (false === in_array($sentPermissionValue, $permissionAllowedValues[$sentPermissionName], true)) {
                $this->context->addViolation($constraint->messageIncorrectValue);

                return;
            }
        }
    }

    /**
     * @param array<string, int>|list<string> $sentPermissions
     */
    private function iterateSentPermissions(array $sentPermissions): iterable
    {
        $listMode = array_is_list($sentPermissions);
        foreach ($sentPermissions as $key => $value) {
            $permissionName = $listMode ? $value : $key;
            $permissionValue = $listMode ? Grants::GRANT_ALLOW : $value;

            yield $permissionName => $permissionValue;
        }
    }
}
