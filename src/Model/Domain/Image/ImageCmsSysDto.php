<?php

declare(strict_types=1);

namespace App\Model\Domain\Image;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use Symfony\Component\Uid\NilUuid;
use Symfony\Component\Uid\Uuid;

final class ImageCmsSysDto
{
    #[Serialize]
    private Uuid $damId;

    #[Serialize]
    private bool $internal = false;

    public function __construct()
    {
        $this->setDamId(NilUuid::v4());
    }

    public function getDamId(): Uuid
    {
        return $this->damId;
    }

    public function setDamId(Uuid $damId): self
    {
        $this->damId = $damId;

        return $this;
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }

    public function setInternal(bool $internal): self
    {
        $this->internal = $internal;

        return $this;
    }
}
