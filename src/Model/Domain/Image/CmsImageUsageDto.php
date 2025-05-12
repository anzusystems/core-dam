<?php

declare(strict_types=1);

namespace App\Model\Domain\Image;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\App;
use Symfony\Component\Uid\NilUuid;
use Symfony\Component\Uid\Uuid;

final class CmsImageUsageDto
{
    #[Serialize]
    private Uuid $damId;

    #[Serialize]
    private int $totalCount = App::ZERO;

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

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function setTotalCount(int $totalCount): self
    {
        $this->totalCount = $totalCount;

        return $this;
    }
}
