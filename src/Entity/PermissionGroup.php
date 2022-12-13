<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\Contracts\Entity\Interfaces\IdentifiableInterface;
use AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface;
use AnzuSystems\Contracts\Entity\Interfaces\UserTrackingInterface;
use AnzuSystems\Contracts\Entity\Traits\IdentityTrait;
use AnzuSystems\Contracts\Entity\Traits\TimeTrackingTrait;
use AnzuSystems\CoreDamBundle\Entity\Traits\UserTrackingTrait;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Security\Permission\DamPermissions;
use App\Repository\PermissionGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

#[ORM\Entity(repositoryClass: PermissionGroupRepository::class)]
class PermissionGroup implements IdentifiableInterface, UserTrackingInterface, TimeTrackingInterface
{
    use IdentityTrait;
    use UserTrackingTrait;
    use TimeTrackingTrait;

    /**
     * User defined title.
     */
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\Length(min: 3, max: 255, minMessage: ValidationException::ERROR_FIELD_LENGTH_MIN, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[Serialize]
    private string $title;

    /**
     * User defined description.
     */
    #[ORM\Column(type: Types::STRING, length: 2_000)]
    #[Assert\Length(max: 255, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[Serialize]
    private string $description;

    /**
     * List of permissions which belongs to permission group.
     */
    #[ORM\Column(type: Types::JSON)]
    #[AppAssert\PermissionValid]
    #[Serialize(strategy: Serialize::KEYS_VALUES)]
    private array $permissions;

    /**
     * List of users who belongs to permission group.
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'permissionGroups', indexBy: 'id')]
    #[Serialize(handler: EntityIdHandler::class, type: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->setTitle('');
        $this->setDescription('');
        $this->setUsers(new ArrayCollection());
        $this->setPermissions(DamPermissions::default());
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
