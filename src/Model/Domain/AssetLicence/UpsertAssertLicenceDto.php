<?php

declare(strict_types=1);

namespace App\Model\Domain\AssetLicence;

use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Entity\User;
use App\Serializer\Handler\Handlers\SsoUserInitializerCollectionHandler;
use Doctrine\Common\Collections\ArrayCollection;

final class UpsertAssertLicenceDto
{
    private const DEFAULT_EXT_SYSTEM_SLUG = 'blog';

    #[Serialize]
    private string $extSystemSlug = self::DEFAULT_EXT_SYSTEM_SLUG;

    #[Serialize]
    private string $extId;

    #[Serialize]
    private bool $limited;

    #[Serialize(handler: SsoUserInitializerCollectionHandler::class)]
    private ArrayCollection $users;
    private ExtSystem $extSystem;

    public function getExtSystemSlug(): string
    {
        return $this->extSystemSlug;
    }

    public function getExtId(): string
    {
        return $this->extId;
    }

    public function setExtId(string $extId): self
    {
        $this->extId = $extId;

        return $this;
    }

    public function isLimited(): bool
    {
        return $this->limited;
    }

    public function setLimited(bool $limited): self
    {
        $this->limited = $limited;

        return $this;
    }

    /**
     * @return ArrayCollection<int, User>
     */
    public function getUsers(): ArrayCollection
    {
        return $this->users;
    }

    public function setUsers(ArrayCollection $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getExtSystem(): ExtSystem
    {
        return $this->extSystem;
    }

    public function setExtSystem(ExtSystem $extSystem): self
    {
        $this->extSystem = $extSystem;

        return $this;
    }
}
