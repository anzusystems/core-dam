<?php

declare(strict_types=1);

namespace App\Distribution\Modules;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionModule;
use AnzuSystems\CoreDamBundle\Distribution\CustomDistributionInterface;
use AnzuSystems\CoreDamBundle\Distribution\DistributionAdapterInterface;
use AnzuSystems\CoreDamBundle\Distribution\DistributionModuleInterface;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Repository\VideoFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Distribution\Modules\Factory\ArtemisVideoDtoFactory;
use App\Domain\ArtemisVideoDistribution\ArtemisVideoDistributionAdapter;
use App\Entity\ArtemisVideoDistribution;
use App\HttpClient\ArtemisClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class ArtemisVideoDistributionModule extends AbstractDistributionModule implements DistributionModuleInterface, CustomDistributionInterface
{
    public function __construct(
        private readonly ArtemisVideoDistributionAdapter $adapter,
        private readonly VideoFileRepository $videoFileRepository,
        private readonly ArtemisVideoDtoFactory $artemisVideoDtoFactory,
        private readonly ArtemisClient $artemisClient,
        private readonly ArtemisMediaDistributionCustomDataFactory $customDataFactory,
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
        $distribution->setDistributionData($this->customDataFactory->createDistributionData($response));
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
