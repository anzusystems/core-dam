<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Validator\Constraints\UniqueEntityDto;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntityDto(entity: User::class, fields: ['email'])]
#[UniqueEntityDto(entity: User::class, fields: ['ssoId'])]
final class CreateUserDto extends AbstractUpsertUserDto
{
    #[Serialize]
    #[Assert\Email(message: ValidationException::ERROR_FIELD_INVALID)]
    private string $email;

    #[Serialize]
    private string $ssoId;

    public function __construct()
    {
        parent::__construct();
        $this->setEmail('');
        $this->setSsoId('');
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

    public function getSsoId(): string
    {
        return $this->ssoId;
    }

    public function setSsoId(string $ssoId): self
    {
        $this->ssoId = $ssoId;

        return $this;
    }
}
