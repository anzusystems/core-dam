<?php

declare(strict_types=1);

namespace App\Domain\ArtemisAudioDistribution;

use AnzuSystems\CoreDamBundle\Distribution\DistributionBroker;
use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use AnzuSystems\CoreDamBundle\Domain\Audio\AudioPublicFacade;
use AnzuSystems\CoreDamBundle\Domain\Audio\AudioPublicManager;
use AnzuSystems\CoreDamBundle\Domain\JwDistribution\JwDistributionManager;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Logger\DamLogger;
use AnzuSystems\CoreDamBundle\Model\Dto\Audio\AudioPublicationAdmDto;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\JwDistributionRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Configuration\ConfigurationProvider;
use App\Entity\ArtemisAudioDistribution;
use App\Repository\ArtemisAudioDistributionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use Throwable;

final class ArtemisAudioDistributionAutomat extends AbstractManager
{
    private const ARTEMIS_AUDIO_DISTRIBUTION_SERVICE = 'artemis_podcast_cms';
    private const JW_AUDIO_DISTRIBUTION_SERVICE = 'jw_cms';

    public function __construct(
        private readonly ArtemisAudioDistributionRepository $repository,
        private readonly JwDistributionRepository $jwDistributionRepository,
        private readonly ArtemisAudioDistributionFactory $factory,
        private readonly ArtemisAudioDistributionManager $artemisAudioDistributionManager,
        private readonly JwDistributionManager $jwDistributionManager,
        private readonly DistributionBroker $distributionBroker,
        private readonly ConfigurationProvider $configurationProvider,
        private readonly DamLogger $logger,
        private readonly AudioPublicFacade $audioPublicFacade,
    ) {
    }

    /**
     * @throws SerializerException
     */
    public function makeAudioPublicUrl(AudioFile $audioFile): void
    {
        // Public url only for processed
        if ($audioFile->getAssetAttributes()->getStatus()->isNot(AssetFileProcessStatus::Processed)) {
            return;
        }

        // Public url only for bonus/premium slot
        if (
            false === $this->isAtPremiumSlot($audioFile) &&
            false === $this->isAtBonusSlot($audioFile)
        ) {
            return;
        }

        $this->tryMakePublic($audioFile);
    }

    /**
     * @throws NonUniqueResultException
     * @throws SerializerException
     */
    public function tryToDistribute(AudioFile $audioFile): void
    {
        if ($audioFile->getAssetAttributes()->getStatus()->isNot(AssetFileProcessStatus::Processed)) {
            return;
        }

        foreach ($audioFile->getAsset()->getEpisodes() as $episode) {
            $distribution = $this->repository->findByEpisodeAndAsset(
                (string) $audioFile->getAsset()->getId(),
                (string) $episode->getId(),
                self::ARTEMIS_AUDIO_DISTRIBUTION_SERVICE
            );

            if (null === $distribution) {
                $this->tryCreateNewDistribution($episode, $audioFile);
            }

            if ($distribution instanceof ArtemisAudioDistribution) {
                $this->tryRedistribute($distribution, $audioFile);
            }
        }
    }

    /**
     * Automatically distribute if audio is synced from RSS to free slot
     *
     * @throws NonUniqueResultException
     * @throws SerializerException
     */
    private function tryCreateNewDistribution(PodcastEpisode $episode, AudioFile $audioFile): void
    {
        if ($this->isAtFreeSlot($audioFile) && $episode->getFlags()->isFromRss()) {
            $premiumVersion = $audioFile->getAsset()->getSlots()->filter(
                fn (AssetSlot $slot): bool => $this->isAtPremiumSlot($slot->getAssetFile())
            )->first();

            if ($premiumVersion instanceof AssetSlot && $premiumVersion->getAudio()) {
                $this->tryMakePublic($premiumVersion->getAudio());
            }

            $artemisAudioDistribution = $this->factory->createFromAudioAndEpisode(
                $audioFile,
                $episode,
                self::ARTEMIS_AUDIO_DISTRIBUTION_SERVICE
            );

            $jwDistribution = $this->prepareJwDistribution($artemisAudioDistribution, $audioFile);
            $this->artemisAudioDistributionManager->create($artemisAudioDistribution, false);

            if ($jwDistribution) {
                $artemisAudioDistribution->addBlockedBy($jwDistribution);
                $this->artemisAudioDistributionManager->flush();
                $this->distributionBroker->startDistribution($jwDistribution);

                return;
            }
            $this->artemisAudioDistributionManager->flush();
            $this->distributionBroker->startDistribution($artemisAudioDistribution);
        }
    }

    private function prepareJwDistribution(ArtemisAudioDistribution $distribution, AudioFile $audioFile): ?JwDistribution
    {
        if (false === $this->configurationProvider->getAudioDistribution()->isRssJwDistribute()) {
            return null;
        }
        $jwDistribution = $this->jwDistributionRepository->findByAssetFileAndDistributionService(
            assetFileId: (string) $audioFile->getAsset()->getId(),
            distributionService: self::JW_AUDIO_DISTRIBUTION_SERVICE
        );

        if ($jwDistribution) {
            return null;
        }

        $jwDistribution = (new JwDistribution())
            ->setAssetId((string) $audioFile->getAsset()->getId())
            ->setAssetFileId((string) $audioFile->getId())
            ->setDistributionService(self::JW_AUDIO_DISTRIBUTION_SERVICE);
        $jwDistribution
            ->getTexts()
            ->setTitle($distribution->getTexts()->getTitle())
            ->setKeywords($distribution->getTexts()->getKeywords())
            ->setAuthor($distribution->getTexts()->getAuthors()[0] ?? '')
            ->setDescription($distribution->getTexts()->getDescription());

        $this->jwDistributionManager->create($jwDistribution, false);

        return $jwDistribution;
    }

    /**
     * Automatically distribute, if audio was uploaded to premium slot and asset was already distributed
     *
     * @throws NonUniqueResultException
     */
    private function tryRedistribute(ArtemisAudioDistribution $distribution, AudioFile $audioFile): void
    {
        if ($this->isAtPremiumSlot($audioFile) && false === $this->isAtBonusSlot($audioFile)) {
            $this->factory->setPremiumDistributionProperties($distribution, $audioFile);
            $this->artemisAudioDistributionManager->flush();

            $this->distributionBroker->redistribute($distribution);
        }
    }

    private function isAtBonusSlot(AssetFile $assetFile): bool
    {
        return $this->isAtSlot($assetFile, $this->configurationProvider->getAudioDistribution()->getAudioBonusSlotName());
    }

    private function isAtPremiumSlot(AssetFile $assetFile): bool
    {
        return $this->isAtSlot($assetFile, $this->configurationProvider->getAudioDistribution()->getAudioPremiumSlotName());
    }

    private function isAtFreeSlot(AssetFile $assetFile): bool
    {
        return $this->isAtSlot($assetFile, $this->configurationProvider->getAudioDistribution()->getAudioFreeSlotName());
    }

    private function isAtSlot(AssetFile $assetFile, string $slotName): bool
    {
        return (bool) $assetFile->getSlots()->filter(
            fn (AssetSlot $slot): bool =>
                $slot->getName() === $slotName
        )->first();
    }

    /**
     * @throws SerializerException
     */
    private function tryMakePublic(AudioFile $audioFile): void
    {
        if (false === $audioFile->getAudioPublicLink()->isPublic()) {
            try {
                $this->audioPublicFacade->makePublic($audioFile, new AudioPublicationAdmDto());
            } catch (Throwable $exception) {
                $this->logger->error(self::class, 'Make public audio link failed', $exception);
            }
        }
    }
}
