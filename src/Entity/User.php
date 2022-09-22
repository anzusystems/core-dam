<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\AuthBundle\Contracts\SsoUserInterface;
use AnzuSystems\Contracts\Entity\Traits\IdentityTrait;
use AnzuSystems\Contracts\Entity\Traits\NamedResourceTrait;
use AnzuSystems\CoreDamBundle\Entity\DamUser;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Permission\UserPermissionResolver;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_email', fields: ['email'])]
class User extends DamUser implements SsoUserInterface, PasswordAuthenticatedUserInterface
{
    use IdentityTrait;
    use NamedResourceTrait;

    public const ID_ANONYMOUS = 1;
    public const ID_CONSOLE = 2;
    public const ID_ADMIN = 3;

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
    #[Serialize(strategy: Serialize::KEYS_VALUES)]
    private array $permissions;

    #[ORM\ManyToMany(targetEntity: PermissionGroup::class, inversedBy: 'users', indexBy: 'id')]
    #[ORM\JoinTable]
    #[Serialize(handler: EntityIdHandler::class, type: PermissionGroup::class)]
    private Collection $permissionGroups;

    public function __construct()
    {
        $this->setEmail('');
        $this->setPassword(null);
        $this->setPermissions([]);
        $this->setApiToken(null);
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

    public function setApiToken(?string $apiToken): self
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

    public function getSsoId(): string
    {
        return (string) $this->getId();
    }

//    public function getUserIdentifier(): string
//    {
//        return $this->getEmail();
//    }
}
