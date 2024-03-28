<?php

declare(strict_types=1);

namespace App\Distribution\Modules;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionModule;
use AnzuSystems\CoreDamBundle\Distribution\CustomDistributionInterface;
use AnzuSystems\CoreDamBundle\Distribution\DistributionAdapterInterface;
use AnzuSystems\CoreDamBundle\Distribution\DistributionModuleInterface;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Distribution\Modules\Factory\ArtemisAudioDtoFactory;
use App\Domain\ArtemisAudioDistribution\ArtemisAudioDistributionAdapter;
use App\Entity\ArtemisAudioDistribution;
use App\HttpClient\ArtemisClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class ArtemisAudioDistributionModule extends AbstractDistributionModule implements DistributionModuleInterface, CustomDistributionInterface
{
    public function __construct(
        private readonly ArtemisClient $artemisRubricClient,
        private readonly AudioFileRepository $audioFileRepository,
        private readonly ArtemisAudioDtoFactory $artemisAudioDtoFactory,
        private readonly ArtemisAudioDistributionAdapter $adapter,
        private readonly ArtemisMediaDistributionCustomDataFactory $customDataFactory,
    ) {
    }

    /**
     * @param ArtemisAudioDistribution $distribution
     *
     * @throws SerializerException
     * @throws TransportExceptionInterface
     */
    public function distribute(Distribution $distribution): void
    {
        $assetFile = $this->audioFileRepository->find($distribution->getAssetFileId());
        if (null === $assetFile) {
            return;
        }

        $mediaDto = $this->artemisAudioDtoFactory->createMediaDto(
            assetFile: $assetFile,
            distribution: $distribution,
        );

        $response = empty($distribution->getExtId())
            ? $this->artemisRubricClient->createMedia($mediaDto)
            : $this->artemisRubricClient->updateMedia($distribution->getExtId(), $mediaDto);

        $distribution->setExtId((string) $response->getMedia()->getId());
        $distribution->setDistributionData($this->customDataFactory->createDistributionData($response));
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

    public function provideAdapter(): DistributionAdapterInterface
    {
        return $this->adapter;
    }
}
