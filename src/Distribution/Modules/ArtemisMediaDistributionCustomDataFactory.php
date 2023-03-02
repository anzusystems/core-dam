<?php

declare(strict_types=1);

namespace App\Distribution\Modules;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionModule;
use AnzuSystems\CoreDamBundle\Distribution\CustomDistributionInterface;
use AnzuSystems\CoreDamBundle\Distribution\DistributionAdapterInterface;
use AnzuSystems\CoreDamBundle\Distribution\DistributionModuleInterface;
use AnzuSystems\CoreDamBundle\Distribution\Modules\AbstractCustomDataFactory;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Entity\YoutubeDistribution;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Distribution\Modules\Factory\ArtemisAudioDtoFactory;
use App\Domain\ArtemisAudioDistribution\ArtemisAudioDistributionAdapter;
use App\Entity\ArtemisAudioDistribution;
use App\HttpClient\ArtemisClient;
use App\Model\Dto\Artemis\ArtemisMediaMetaDto;
use App\Model\Dto\Artemis\ArtemisMediaResponseDto;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
