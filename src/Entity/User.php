<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\AuthBundle\Contracts\AnzuAuthUserInterface;
use AnzuSystems\AuthBundle\Contracts\ApiTokenUserInterface;
use AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface;
use AnzuSystems\Contracts\Entity\Interfaces\UserTrackingInterface;
use AnzuSystems\Contracts\Entity\Traits\TimeTrackingTrait;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Entity\DamUser;
use AnzuSystems\CoreDamBundle\Entity\Traits\PersonNameTrait;
use AnzuSystems\CoreDamBundle\Entity\Traits\UserTrackingTrait;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Security\Permission\UserPermissionResolver;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Validator\Constraints as AppAssert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_email', fields: ['email'])]
class User extends DamUser implements
    AnzuAuthUserInterface,
    PasswordAuthenticatedUserInterface,
    ApiTokenUserInterface,
    UserTrackingInterface,
    TimeTrackingInterface
{
    use UserTrackingTrait;
    use TimeTrackingTrait;
    use PersonNameTrait;

    public const ID_ANONYMOUS = 1;
    public const ID_CONSOLE = 2;
    public const ID_ADMIN = 3;
    public const ID_BASIC_USER = 4;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    #[Serialize]
    protected ?int $id = null;

    /**
     * Unique Email of user.
     */
    #[ORM\Column(type: Types::STRING, length: 180)]
    #[Serialize]
    private string $email;

    /**
     * Authorization password for system users.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $password;

    /**
     * Authorization token for system users. Required to access /api/sys/* endpoints.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $apiToken;

    /**
     * List of permissions which belongs to user.
     *
     * @var array<string, int>
     */
    #[ORM\Column(type: Types::JSON)]
    #[AppAssert\PermissionValid(requireAll: false)]
    #[Serialize(strategy: Serialize::KEYS_VALUES)]
    private array $permissions;

    #[ORM\ManyToMany(targetEntity: PermissionGroup::class, inversedBy: 'users', fetch: App::DOCTRINE_EXTRA_LAZY, indexBy: 'id')]
    #[ORM\JoinTable]
    #[Serialize(handler: EntityIdHandler::class, type: PermissionGroup::class)]
    private Collection $permissionGroups;

    public function __construct()
    {
        $this->setId(null);
        $this->setEmail('');
        $this->setPassword(null);
        $this->setPermissions([]);
        $this->setApiToken(null);
        $this->setEnabled(true);
        $this->setRoles([self::ROLE_USER]);
        $this->setPermissionGroups(new ArrayCollection());
        $this->setAssetLicences(new ArrayCollection());
        $this->setAdminToExtSystems(new ArrayCollection());
        $this->setUserToExtSystems(new ArrayCollection());
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): static
    {
        $this->apiToken = $apiToken;

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
     * @return Collection<int, PermissionGroup>
     */
    public function getPermissionGroups(): Collection
    {
        return $this->permissionGroups;
    }

    public function setPermissionGroups(Collection $permissionGroups): self
    {
        $this->permissionGroups = $permissionGroups;

        return $this;
    }

    #[Serialize(strategy: Serialize::KEYS_VALUES)]
    public function getResolvedPermissions(): array
    {
        return UserPermissionResolver::resolve($this);
    }

    #[Serialize]
    public function getPermissionGroupTitles(): array
    {
        return $this
            ->getPermissionGroups()
            ->map(fn (PermissionGroup $permissionGroup): string => $permissionGroup->getTitle())
            ->toArray()
        ;
    }

    public function getAuthId(): string
    {
        return (string) $this->getId();
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function addRole(string $role): self
    {
        if (false === $this->hasRole($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        $foundKey = array_search($role, $this->roles, true);
        if (is_int($foundKey)) {
            unset($this->roles[$foundKey]);
        }

        return $this;
    }
}
