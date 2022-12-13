<?php

declare(strict_types=1);

namespace App\Model\Domain\PermissionGroup;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Entity\PermissionGroup;
use Doctrine\Common\Collections\ArrayCollection;

final class PermissionGroupCollectionDto
{
    /**
     * Permission group collection.
     */
    #[Serialize(handler: EntityIdHandler::class, type: PermissionGroup::class)]
    private ArrayCollection $permissionGroups;

    public function __construct()
    {
        $this->setPermissionGroups(new ArrayCollection());
    }

    public function getPermissionGroups(): ArrayCollection
    {
        return $this->permissionGroups;
    }

    public function setPermissionGroups(ArrayCollection $permissionGroups): self
    {
        $this->permissionGroups = $permissionGroups;

        return $this;
    }
}
