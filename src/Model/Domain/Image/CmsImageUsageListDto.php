<?php

declare(strict_types=1);

namespace App\Model\Domain\Image;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\App;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class CmsImageUsageListDto
{
    #[Serialize]
    private int $totalCount = App::ZERO;

    #[Serialize(type: CmsImageUsageDto::class)]
    private Collection $data;

    public function __construct()
    {
        $this->setData(new ArrayCollection());
    }

    /**
     * @return Collection<array-key, CmsImageUsageDto>
     */
    public function getData(): Collection
    {
        return $this->data;
    }

    public function setData(Collection $data): self
    {
        $this->data = $data;

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
