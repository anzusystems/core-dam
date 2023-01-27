<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Validator\Constraints\UniqueEntityDto;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntityDto(entity: User::class, fields: ['email'])]
#[UniqueEntityDto(entity: User::class, fields: ['id'])]
final class CreateUserDto extends AbstractUpsertUserDto
{
    #[Serialize]
    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    private int $id;

    #[Serialize]
    #[Assert\Email(message: ValidationException::ERROR_FIELD_INVALID)]
    private string $email;

    public function __construct()
    {
        parent::__construct();
        $this->setId(0);
        $this->setEmail('');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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
