<?php

declare(strict_types=1);

namespace App\Domain\ArtemisVideoDistribution;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionModule;
use AnzuSystems\CoreDamBundle\Distribution\CustomDistributionInterface;
use AnzuSystems\CoreDamBundle\Distribution\DistributionAdapterInterface;
use AnzuSystems\CoreDamBundle\Distribution\DistributionModuleInterface;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Repository\VideoFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Distribution\Modules\Factory\ArtemisVideoDtoFactory;
use App\Entity\ArtemisVideoDistribution;
use App\HttpClient\ArtemisClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class ArtemisVideoDistributionModule extends AbstractDistributionModule implements DistributionModuleInterface, CustomDistributionInterface
{
    public const ARTICLE_WEB_URL = 'articleWebUrl';
    public const ARTICLE_ADMIN_URL = 'articleAdminUrl';
    public const MEDIA_ADMIN_URL = 'mediaAdminUrl';
    public const ARTICLE_ID = 'articleId';

    public function __construct(
        private readonly ArtemisVideoDistributionAdapter $adapter,
        private readonly VideoFileRepository $videoFileRepository,
        private readonly ArtemisVideoDtoFactory $artemisVideoDtoFactory,
        private readonly ArtemisClient $artemisClient,
    ) {
    }

    /**
     * @param ArtemisVideoDistribution $distribution
     *
     * @throws SerializerException
     * @throws TransportExceptionInterface
     */
    public function distribute(Distribution $distribution): void
    {
        $assetFile = $this->videoFileRepository->find($distribution->getAssetFileId());
        if (null === $assetFile) {
            return;
        }

        $mediaDto = $this->artemisVideoDtoFactory->createMediaDto(
            assetFile: $assetFile,
            distribution: $distribution,
        );

        $response = empty($distribution->getExtId())
            ? $this->artemisClient->createMedia($mediaDto)
            : $this->artemisClient->updateMedia($distribution->getExtId(), $mediaDto);

        $distribution->setExtId($response->getMedia()->getExternalId());
        $distribution->setDistributionData([
            self::ARTICLE_WEB_URL => $response->getMeta()->getArticleUrl(),
            self::ARTICLE_ADMIN_URL => $response->getMeta()->getArticleAdminUrl(),
            self::MEDIA_ADMIN_URL => $response->getMeta()->getMediaAdminUrl(),
        ]);
    }

    public function supportsAssetType(): array
    {
        return [
            AssetType::Video,
        ];
    }

    public static function getDefaultKeyName(): string
    {
        return self::class;
    }

    public function isAuthenticated(string $distributionService): bool
    {
        return true;
    }

    public function provideAdapter(): DistributionAdapterInterface
    {
        return $this->adapter;
    }
}
