<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueEntityDtoValidator extends ConstraintValidator
{
    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param UniqueEntityDto $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $fieldsNames = $constraint->fields;
        $entity = $constraint->entity;
        $fields = [];
        foreach ($fieldsNames as $fieldName) {
            $fields[$fieldName] = $this->propertyAccessor->getValue($value, $fieldName);
        }
        if ($this->entityManager->getRepository($entity)->count($fields)) {
            foreach ($fieldsNames as $fieldsName) {
                $this->context->buildViolation($constraint->message)->atPath($fieldsName)->addViolation();
            }
        }
    }
}
