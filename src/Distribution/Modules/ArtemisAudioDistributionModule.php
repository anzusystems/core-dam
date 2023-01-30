<?php

declare(strict_types=1);

namespace App\Distribution\Modules;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionModule;
use AnzuSystems\CoreDamBundle\Distribution\DistributionModuleInterface;
use AnzuSystems\CoreDamBundle\Entity\CustomDistribution;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use App\Distribution\Modules\Artemis\ArtemisAudioDtoFactory;
use App\HttpClient\ArtemisClient;

final class ArtemisAudioDistributionModule extends AbstractDistributionModule implements DistributionModuleInterface
{
    private const ARTICLE_WEB_URL = 'articleWebUrl';
    private const ARTICLE_ADMIN_URL = 'articleAdminUrl';
    private const MEDIA_ADMIN_URL = 'mediaAdminUrl';

    public function __construct(
        private readonly ArtemisClient $artemisRubricClient,
        private readonly AudioFileRepository $audioFileRepository,
        private readonly ArtemisAudioDtoFactory $artemisAudioDtoFactory,
    ) {
    }

    /**
     * @param CustomDistribution $distribution
     */
    public function distribute(Distribution $distribution): void
    {
        $assetFile = $this->assetFileRepository->find($distribution->getAssetFileId());
        if (null === $assetFile) {
            return;
        }

        $mediaDto = $this->artemisAudioDtoFactory->createMediaDto(
            $assetFile,
            $distribution
        );

        //        $this->artemisRubricClient->createMedia($mediaDto);
    }

    public function redistribute(Distribution $distribution): void
    {
    }

    public function supportsAssetType(): array
    {
        return [
            AssetType::Audio,
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
}
