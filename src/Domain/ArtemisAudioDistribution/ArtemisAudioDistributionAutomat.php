<?php

declare(strict_types=1);

namespace App\Domain\ArtemisAudioDistribution;

use AnzuSystems\CoreDamBundle\Distribution\DistributionBroker;
use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use AnzuSystems\CoreDamBundle\Domain\Audio\AudioPublicManager;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Model\Dto\Audio\AudioPublicationAdmDto;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use App\Configuration\ConfigurationProvider;
use App\Repository\ArtemisAudioDistributionRepository;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;

final class ArtemisAudioDistributionAutomat extends AbstractManager
{
    public function __construct(
        private readonly ArtemisAudioDistributionRepository $repository,
        private readonly ArtemisAudioDistributionFactory $factory,
        private readonly ArtemisAudioDistributionManager $artemisAudioDistributionManager,
        private readonly DistributionBroker $distributionBroker,
        private readonly ConfigurationProvider $configurationProvider,
        private readonly AudioPublicManager $audioPublicManager,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws FilesystemException
     */
    public function tryToDistribute(AudioFile $audioFile): void
    {
        if ($audioFile->getAssetAttributes()->getStatus()->isNot(AssetFileProcessStatus::Processed)) {
            return;
        }

        foreach ($audioFile->getAsset()->getEpisodes() as $episode) {
            if (false === $this->isRssEpisode($episode)) {
                continue;
            }

            // todo validate multiple distributions result
            $distribution = $this->repository->findByPodcastAndAsset(
                (string) $audioFile->getAsset()->getId(),
                (string) $episode->getPodcast()->getId(),
                'artemis_podcast_cms'
            );

            if (null === $distribution && $this->isAtFreeSlot($audioFile)) {
                $distribution = $this->artemisAudioDistributionManager->create(
                    $this->factory->createFromAudioAndEpisode($audioFile, $episode, 'artemis_podcast_cms'),
                );

                $this->distributionBroker->startDistribution($distribution);
            }

            // todo trigger on set position!
            if ($distribution && $this->isAtPremiumSlot($audioFile)) {
                if (false === $audioFile->getAudioPublicLink()->isPublic()) {
                    $this->audioPublicManager->makePublic($audioFile, (new AudioPublicationAdmDto()));
                }
                $this->factory->setPremiumDistributionProperties($distribution, $audioFile);
                $this->artemisAudioDistributionManager->flush();

                $this->distributionBroker->redistribute($distribution);
            }
        }
    }

    private function isRssEpisode(PodcastEpisode $episode): bool
    {
        return false === (empty($episode->getAttributes()->getRssUrl()) && empty($episode->getAttributes()->getRssId()));
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
}
