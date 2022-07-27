<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Anzu\CommonBundle\AnzuSerializer\Attributes\AnzuSerialize;
use Anzu\CommonBundle\AnzuSerializer\Handler\Handlers\IdentifiableHandler;
use Anzu\CommonBundle\Entity\AnzuUser;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait UserTrackingTrait
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn]
    #[AnzuSerialize(handler: IdentifiableHandler::class)]
    protected User $createdBy;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn]
    #[AnzuSerialize(handler: IdentifiableHandler::class)]
    protected User $modifiedBy;

    /**
     * @psalm-suppress TypeDoesNotContainType
     * @psalm-suppress RedundantPropertyInitializationCheck
     */
    public function __initializeUsers(): void
    {
        if (false === isset($this->createdBy)) {
            $this->createdBy = new User();
        }
        if (false === isset($this->modifiedBy)) {
            $this->modifiedBy = new User();
        }
    }

    /**
     * @psalm-suppress PropertyTypeCoercion
     * @psalm-suppress RedundantPropertyInitializationCheck
     */
    public function getCreatedBy(): User
    {
        if (false === isset($this->createdBy)) {
            $this->setCreatedBy(new User());
        }

        return $this->createdBy;
    }

    /**
     * @psalm-suppress PropertyTypeCoercion
     */
    public function setCreatedBy(User|AnzuUser $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @psalm-suppress PropertyTypeCoercion
     * @psalm-suppress RedundantPropertyInitializationCheck
     */
    public function getModifiedBy(): User
    {
        if (false === isset($this->modifiedBy)) {
            $this->setModifiedBy(new User());
        }

        return $this->modifiedBy;
    }

    /**
     * @psalm-suppress PropertyTypeCoercion
     */
    public function setModifiedBy(User|AnzuUser $modifiedBy): static
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }
}
