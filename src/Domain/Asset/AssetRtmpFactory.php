<?php

declare(strict_types=1);

namespace App\Domain\Asset;

use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileFactory;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\Helper\FileNameHelper;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use AnzuSystems\CoreDamBundle\Repository\KeywordRepository;
use App\App;
use App\Configuration\ConfigurationProvider;
use App\Model\Dto\Asset\RtmpAssetDto;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;

final readonly class AssetRtmpFactory
{
    private const string DATE_TIME_FORMAT = 'd.m.Y H:i';

    public function __construct(
        private AssetLicenceRepository $assetLicenceRepository,
        private KeywordRepository $keywordRepository,
        private AssetFileFactory $assetFileFactory,
        private ConfigurationProvider $configurationProvider,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws FilesystemException
     */
    public function createFromRtmp(RtmpAssetDto $dto): AssetFile
    {
        $rtmpConfig = $this->configurationProvider->getRtmpConfiguration();
        $licence = $this->assetLicenceRepository->find($rtmpConfig->getAssetLicenceId());

        if (null === $licence) {
            throw new DomainException(
                sprintf(
                    'Asset licence id (%s) not found',
                    $rtmpConfig->getAssetLicenceId()
                )
            );
        }

        $assetFile = $this->assetFileFactory->createAssetFileForStorage(
            storageName: $rtmpConfig->getStorageName(),
            filePath: $dto->getPath(),
            licence: $licence
        );
        $this->setupMetadata($assetFile, $dto);

        return $assetFile;
    }

    private function setupMetadata(AssetFile $assetFile, RtmpAssetDto $dto): void
    {
        $rtmpConfig = $this->configurationProvider->getRtmpConfiguration();
        $fileName = FileNameHelper::getFilenameWithoutExtension($dto->getPath());
        $parts = explode('-', $fileName, 2);
        $timeStamp = $parts[1] ?? null;

        $recordedAt = $timeStamp
            ? DateTimeImmutable::createFromMutable(
                (new DateTime())
                    ->setTimestamp((int) $timeStamp)
            )
                ->setTimezone(new DateTimeZone(App::DATE_TIME_ZONE))
            : App::getAppDate()
                ->setTimezone(new DateTimeZone(App::DATE_TIME_ZONE));

        $assetFile->getAsset()->getMetadata()->setCustomData([
            'title' => sprintf($rtmpConfig->getTitleTemplate(), $recordedAt->format(self::DATE_TIME_FORMAT)),
        ]);

        $keyword = $this->keywordRepository->find($rtmpConfig->getKeywordId());
        if ($keyword) {
            $assetFile->getAsset()->getKeywords()->add($keyword);
        }
    }
}
