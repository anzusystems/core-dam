<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\Contracts\Entity\AnzuPermissionGroup;
use AnzuSystems\CoreDamBundle\Entity\Traits\UserTrackingTrait;
use AnzuSystems\CoreDamBundle\Validator\Constraints\UniqueEntity;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Repository\PermissionGroupRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionGroupRepository::class)]
#[UniqueEntity(fields: ['title'])]
class PermissionGroup extends AnzuPermissionGroup
{
    use UserTrackingTrait;

    /**
     * List of users who belongs to permission group.
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'permissionGroups', indexBy: 'id')]
    #[Serialize(handler: EntityIdHandler::class, type: User::class)]
    protected Collection $users;
}
