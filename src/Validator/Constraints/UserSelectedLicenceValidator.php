<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use AnzuSystems\CommonBundle\Domain\User\CurrentAnzuUserProvider;
use App\Entity\User;
use App\Model\Domain\User\UpdateCurrentUserDto;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class UserSelectedLicenceValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CurrentAnzuUserProvider $currentAnzuUserProvider,
    ) {
    }

    /**
     * @param UserSelectedLicence $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (false === ($value instanceof UpdateCurrentUserDto)) {
            throw new UnexpectedTypeException($constraint, UpdateCurrentUserDto::class);
        }

        if (null === $value->getSelectedLicence()) {
            return;
        }

        /** @var User $user */
        $user = $this->currentAnzuUserProvider->getCurrentUser();
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return;
        }
        if ($user->getAdminToExtSystems()->contains($value->getSelectedLicence()->getExtSystem())) {
            return;
        }

        if (false === $user->getAssetLicences()->contains($value->getSelectedLicence())) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('selectedLicence')
                ->addViolation()
            ;
        }
    }
}
