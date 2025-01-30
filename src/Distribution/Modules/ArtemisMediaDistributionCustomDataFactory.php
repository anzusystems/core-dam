<?php

declare(strict_types=1);

namespace App\Distribution\Modules;

use AnzuSystems\CoreDamBundle\Distribution\Modules\AbstractCustomDataFactory;
use App\Model\Domain\Distribution\ArtemisDistributionData;
use App\Model\Dto\Artemis\ArtemisMediaResponseDto;

final class ArtemisMediaDistributionCustomDataFactory extends AbstractCustomDataFactory
{
    public function createDistributionData(ArtemisMediaResponseDto $dto): array
    {
        $data = new ArtemisDistributionData();
        $data->getArticleWebUrl()->setValue($dto->getMeta()->getArticleUrl());
        $data->getArticleAdminUrl()->setValue($dto->getMeta()->getArticleAdminUrl());
        $data->getMediaAdminUrl()->setValue($dto->getMeta()->getMediaAdminUrl());

        /** @var array $array */
        $array = $this->serializer->toArray($data);

        return $array;
    }
}
