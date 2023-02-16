<?php

declare(strict_types=1);

namespace App\Domain\ArtemisAudioDistribution;

use AnzuSystems\CoreDamBundle\Cache\AudioRouteGenerator;
use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionDtoFactory;
use AnzuSystems\CoreDamBundle\Domain\Distribution\DistributionBodyBuilder;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use App\Configuration\ConfigurationProvider;
use App\Entity\ArtemisAudioDistribution;
use Doctrine\ORM\NonUniqueResultException;

final class ArtemisAudioDistributionFactory extends AbstractDistributionDtoFactory
{
    public function __construct(
        private readonly AudioRouteGenerator $audioRouteGenerator,
        private readonly ConfigurationProvider $configurationProvider,
        private readonly DistributionBodyBuilder $distributionBodyBuilder,
    ) {
    }


    /**
     * Creates minimal version of Distribution used to automatic updates to CMS
     *
     * @throws NonUniqueResultException
     */
    public function createFromAudioAndEpisode(AudioFile $audioFile, PodcastEpisode $episode, string $service): ArtemisAudioDistribution
    {
        $audioDistribution = (new ArtemisAudioDistribution())
            ->setDistributionService($service);

        $this->distributionBodyBuilder->setWriterProperties(
            distributionService: $service,
            assetFile: $audioFile->getAsset(),
            object: $audioDistribution
        );

        $audioDistribution->getFlags()->setCreateArticle(false);
        $audioDistribution->getTexts()->setEpisodeId((string) $episode->getId());

        $audioDistribution->setAssetId((string) $audioFile->getAsset()->getId());
        $audioDistribution->setAssetFileId((string) $audioFile->getId());

        $this->setFreeDistributionProperties($audioDistribution, $episode);
        $this->setPremiumDistributionProperties($audioDistribution, $audioFile);
        $this->setRubricId($audioDistribution, $audioFile);

        return $audioDistribution;
    }

    public function createFromAudioFile(AudioFile $audioFile, string $service): ArtemisAudioDistribution
    {
        $audioDistribution = (new ArtemisAudioDistribution())
            ->setDistributionService($service);

        $this->setPremiumDistributionProperties($audioDistribution, $audioFile);
        $episode = $this->getRssEpisode($audioFile);
        if ($episode) {
            $this->setFreeDistributionProperties($audioDistribution, $episode);
        }
        $this->setRubricId($audioDistribution, $audioFile);

        return $audioDistribution;
    }

    /**
     * Set premium properties from premium audio file
     */
    public function setPremiumDistributionProperties(
        ArtemisAudioDistribution $audioDistribution,
        AudioFile $audioFile,
    ): void {
        $premiumFile = $this->getPremiumAssetFile($audioFile->getAsset());

        if ($premiumFile && $premiumFile->getAudioPublicLink()->isPublic()) {
            $audioDistribution->getTexts()->setPremiumUrl(
                $this->audioRouteGenerator->getFullUrl(
                    path: $premiumFile->getAudioPublicLink()->getPath(),
                    extSlug: $premiumFile->getExtSystem()->getSlug()
                )
            );
        }
    }

    /**
     * Sets rubricId based on distribution category. If option is missing, uses configuration value
     */
    private function setRubricId(
        ArtemisAudioDistribution $audioDistribution,
        AudioFile $audioFile,
    ): void {
        $option = $this->getSelectedOption($audioFile, $audioDistribution);
        if ($option) {
            $audioDistribution->getTexts()->setRubricId((int) $option->getValue());

            return;
        }

        $audioDistribution->getTexts()->setRubricId($this->configurationProvider->getAudioDistribution()->getDefaultRubricId());
    }

    /**
     * Sets free distribution properties from RSS episode
     */
    private function setFreeDistributionProperties(
        ArtemisAudioDistribution $audioDistribution,
        PodcastEpisode $episode,
    ): void {
        $audioDistribution->getTexts()
            ->setFreeUrl($episode->getAttributes()->getRssUrl())
            ->setExtRssId($episode->getAttributes()->getRssId());

        $audioDistribution->getTexts()->setEpisodeId((string) $episode->getId());
        $audioDistribution->getTexts()->setPodcastId((string) $episode->getPodcast()->getId());
    }

    private function getPremiumAssetFile(Asset $asset): ?AudioFile
    {
        $config = $this->configurationProvider->getAudioDistribution();

        foreach ($asset->getSlots() as $slot) {
            $audioFile = $slot->getAssetFile();

            if ($config->getAudioPremiumSlotName() === $slot->getName() && $audioFile instanceof AudioFile) {
                return $audioFile;
            }
        }

        return null;
    }

    private function getRssEpisode(AssetFile $assetFile): ?PodcastEpisode
    {
        $episode = $assetFile->getAsset()->getEpisodes()
            ->filter(
                fn (PodcastEpisode $episode): bool =>
                    false === empty($episode->getAttributes()->getRssId()) &&
                    false === empty($episode->getAttributes()->getRssUrl())
            )->first();

        if ($episode instanceof PodcastEpisode) {
            return $episode;
        }

        return null;
    }
}
