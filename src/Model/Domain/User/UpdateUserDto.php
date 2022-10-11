<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

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

    #[AppAssert\PermissionValid(requireAll: false)]
    #[Serialize(strategy: Serialize::KEYS_VALUES)]
    private array $permissions;

    public function __construct()
    {
        parent::__construct();
        $this->setPermissions([]);
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }
}
