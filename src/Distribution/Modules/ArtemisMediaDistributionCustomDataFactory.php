<?php

declare(strict_types=1);

namespace App\Distribution\Modules;

use AnzuSystems\CoreDamBundle\Distribution\Modules\AbstractCustomDataFactory;
use App\Model\Dto\Artemis\ArtemisMediaResponseDto;

final class ArtemisMediaDistributionCustomDataFactory extends AbstractCustomDataFactory
{
    public const ARTICLE_WEB_URL = 'articleWebUrl';
    public const ARTICLE_ADMIN_URL = 'articleAdminUrl';
    public const MEDIA_ADMIN_URL = 'mediaAdminUrl';

    public function createDistributionData(ArtemisMediaResponseDto $dto): array
    {
        return [
            self::ARTICLE_WEB_URL => $this->createUrl($dto->getMeta()->getArticleUrl()),
            self::ARTICLE_ADMIN_URL => $this->createUrl($dto->getMeta()->getArticleAdminUrl()),
            self::MEDIA_ADMIN_URL => $this->createUrl($dto->getMeta()->getMediaAdminUrl()),
        ];
    }
}
