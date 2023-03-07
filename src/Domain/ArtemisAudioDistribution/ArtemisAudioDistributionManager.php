<?php

declare(strict_types=1);

namespace App\Domain\ArtemisAudioDistribution;

use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use AnzuSystems\CoreDamBundle\Domain\AssetLicence\AssetLicenceManager as BaseAssetLicenceManager;
use AnzuSystems\CoreDamBundle\Domain\Distribution\AbstractDistributionManager;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use App\Entity\ArtemisAudioDistribution;
use App\Model\Domain\AssetLicence\UpsertAssertLicenceDto;

final class ArtemisAudioDistributionManager extends AbstractDistributionManager
{
    /**
     * @param ArtemisAudioDistribution $distribution
     * @param ArtemisAudioDistribution $newDistribution
     */
    public function update(Distribution $distribution, Distribution $newDistribution, bool $flush = true): Distribution
    {
        $this->trackModification($distribution);

        $distribution->getTexts()
            ->setTitle($newDistribution->getTexts()->getTitle())
            ->setExtRssId($newDistribution->getTexts()->getExtRssId())
            ->setDescription($newDistribution->getTexts()->getDescription())
            ->setFreeUrl($newDistribution->getTexts()->getFreeUrl())
            ->setPremiumUrl($newDistribution->getTexts()->getPremiumUrl())
            ->setAuthors($newDistribution->getTexts()->getAuthors())
            ->setKeywords($newDistribution->getTexts()->getKeywords())
            ->setRubricId($newDistribution->getTexts()->getRubricId())
            ->setEpisodeId($newDistribution->getTexts()->getEpisodeId())
            ->setPodcastId($newDistribution->getTexts()->getPodcastId());
        $distribution->getFlags()
            ->setCreateArticle($newDistribution->getFlags()->isCreateArticle());
        $distribution->setPublishAt($newDistribution->getPublishAt());

        $this->flush($flush);

        return $distribution;
    }

    public static function getDefaultKeyName(): string
    {
        return ArtemisAudioDistribution::class;
    }
}
