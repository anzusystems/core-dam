<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Validator\Constraints\UniqueEntityDto;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntityDto(entity: User::class, fields: ['email'])]
final class CreateUserDto extends AbstractUserDto
{
    #[Serialize]
    #[Assert\NotCompromisedPassword(message: ValidationException::ERROR_COMPROMISED_PASSWORD)]
    #[Assert\Length(
        min: 8,
        minMessage: ValidationException::ERROR_FIELD_LENGTH_MIN,
    )]
    protected string $plainPassword;

    #[Serialize]
    #[Assert\Email(message: ValidationException::ERROR_FIELD_INVALID)]
    private string $email;

    public function __construct()
    {
        parent::__construct();
        $this->setEmail('');
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
