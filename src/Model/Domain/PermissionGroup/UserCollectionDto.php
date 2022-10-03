<?php

declare(strict_types=1);

namespace App\Model\Domain\PermissionGroup;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class UserCollectionDto
{
    /**
     * User collection.
     */
    #[Serialize(handler: EntityIdHandler::class, type: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->setUsers(new ArrayCollection());
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUsers(Collection $users): self
    {
        $this->users = $users;

        return $this;
    }
}
