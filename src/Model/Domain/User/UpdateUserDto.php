<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Validator\Constraints as AppAssert;

final class UpdateUserDto extends AbstractUserDto
{
    #[AppAssert\PermissionValid(requireAll: false)]
    #[Serialize(strategy: Serialize::KEYS_VALUES)]
    private array $permissions;

    public function __construct()
    {
        parent::__construct();
        $this->setPermissions([]);
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
