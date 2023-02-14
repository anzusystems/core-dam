<?php

declare(strict_types=1);

namespace App\Validator;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class IterableValidator
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @template T
     * @template TKey of array-key
     *
     * @param ArrayCollection<TKey, T> $collection
     *
     * @throws ValidationException
     */
    public function validateDtoItems(ArrayCollection $collection): void
    {
        $violationList = new ConstraintViolationList();
        $violationList->addAll(
            $this->validator->validate($collection)
        );
        if ($violationList->count()) {
            throw new ValidationException($violationList);
        }
    }
}
