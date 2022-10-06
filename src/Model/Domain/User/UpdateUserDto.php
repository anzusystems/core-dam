<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateUserDto extends AbstractUserDto
{
    #[Serialize]
    #[Assert\NotCompromisedPassword(message: ValidationException::ERROR_COMPROMISED_PASSWORD)]
    #[Assert\AtLeastOneOf([
        new Assert\Blank(),
        new Assert\Length(
            min: 8,
            minMessage: ValidationException::ERROR_FIELD_LENGTH_MIN,
        )
    ], includeInternalMessages: false)]
    protected string $plainPassword;
}
