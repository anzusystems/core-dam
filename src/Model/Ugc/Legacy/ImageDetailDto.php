<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy;

use AnzuSystems\CoreDamBundle\Entity\RegionOfInterest;
use AnzuSystems\CoreDamBundle\Model\Dto\RegionOfInterest\RegionOfInterestAdmDetailDto;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

class ImageDetailDto extends ImageListDto
{
    #[Serialize(handler: EntityIdHandler::class, type: RegionOfInterest::class)]
    public function getRegionsOfInterest(): Collection
    {
        return $this->imageFile->getRegionsOfInterest();
    }

    #[Serialize]
    public function getDefaultRegionOfInterest(): ?RegionOfInterestAdmDetailDto
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('position', RegionOfInterest::FIRST_ROI_POSITION))
            ->setMaxResults(1);

        $regionOfInterest = $this->getRegionsOfInterest()->matching($criteria)->first();

        return $regionOfInterest ? RegionOfInterestAdmDetailDto::getInstance($regionOfInterest) : null;
    }
}
