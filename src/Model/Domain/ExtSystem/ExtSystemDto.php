<?php

declare(strict_types=1);

namespace App\Model\Domain\ExtSystem;

use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;

final class ExtSystemDto
{
    private ExtSystem $extSystem;

    public static function getInstance(ExtSystem $extSystem): self
    {
        return (new self())
            ->setExtSystem($extSystem)
        ;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getExtSystem(): ExtSystem
    {
        return $this->extSystem;
    }

    public function setExtSystem(ExtSystem $extSystem): self
    {
        $this->extSystem = $extSystem;

        return $this;
    }

    #[Serialize]
    public function getName(): string
    {
        return $this->extSystem->getName();
    }
}
