<?php

declare(strict_types=1);


namespace App\Tests\Distribution;

use AnzuSystems\CoreDamBundle\DataFixtures\AudioFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PodcastFixtures;
use AnzuSystems\CoreDamBundle\Domain\Audio\AudioPositionFacade;
use AnzuSystems\CoreDamBundle\Domain\Podcast\RssImportManager;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use App\Domain\ArtemisAudioDistribution\ArtemisAudioDistributionAutomat;
use App\Entity\ArtemisAudioDistribution;
use App\Tests\Controller\AbstractControllerTest;

final class ArtemisAudioDistributionTest extends AbstractControllerTest
{
    private RssImportManager $importManager;
    private AudioPositionFacade $audioPositionFacade;
    private ArtemisAudioDistributionAutomat $automat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importManager = $this->getService(RssImportManager::class);
        $this->audioPositionFacade = $this->getService(AudioPositionFacade::class);
        $this->automat = $this->getService(ArtemisAudioDistributionAutomat::class);
    }

    public function testBonusEpisodeDistribution(): void {
        $audioFile = $this->entityManager->find(AudioFile::class, AudioFixtures::AUDIO_ID_1);

        $this->automat->makeAudioPublicUrl($audioFile);
        $this->assertSame(false, $audioFile->getAudioPublicLink()->isPublic());

        $this->audioPositionFacade->setToSlot($audioFile->getAsset(), $audioFile, 'bonus');
        $this->automat->makeAudioPublicUrl($audioFile);
        $this->assertSame(true, $audioFile->getAudioPublicLink()->isPublic());

    }

    public function testDistribution(): void {
        $podcast = $this->entityManager->getRepository(Podcast::class)->find(PodcastFixtures::PODCAST_1);

        // Run synchronization procedure for podcasts
        $this->importManager->syncPodcast($podcast);

        // Find RSS Episode created by RSS synchronization, validated preview Image
        $episode = $this->entityManager->getRepository(PodcastEpisode::class)->findOneBy([
            'podcast' => $podcast,
            'attributes.rssId' => '45238 at https://www.thisamericanlife.org'
        ]);
        $this->assertNotNull($episode);
        $this->assertNotNull($episode->getImagePreview());
        $this->assertNotNull($podcast->getImagePreview());
        $this->assertSame(
            $episode->getImagePreview()->getImageFile(),
            $podcast->getImagePreview()->getImageFile()
        );

        $distribution = $this->entityManager->getRepository(ArtemisAudioDistribution::class)->findOneBy([
            'texts.podcastId' => $episode->getPodcast()->getId()
        ]);
        $this->assertNotNull($distribution);
        $this->assertSame('artemis_podcast_cms', $distribution->getDistributionService());
        $this->validateFreeDistribution($distribution);

        $audioFile = $this->entityManager->getRepository(AudioFile::class)->find(AudioFixtures::AUDIO_ID_1);
        $this->audioPositionFacade->setToSlot($episode->getAsset(), $audioFile, 'premium');
        // manually triggers "processed" state
        $this->automat->makeAudioPublicUrl($audioFile);
        $this->automat->tryToDistribute($audioFile);
        $this->entityManager->refresh($distribution);
        $this->validatePremiumDistribution($distribution);
    }

    private function validateFreeDistribution(ArtemisAudioDistribution $distribution): void
    {
        $this->assertSame('artemis_podcast_cms', $distribution->getDistributionService());
        $this->assertSame('789: The Runaround', $distribution->getTexts()->getTitle());
        $this->assertSame('45238 at https://www.thisamericanlife.org', $distribution->getTexts()->getExtRssId());
        $this->assertSame('People being dodged, delayed, and evaded—and what they do to put an end to it.', $distribution->getTexts()->getDescription());
        $this->assertSame('http://core-dam.sme.localhost/download-file?file=audioa', $distribution->getTexts()->getFreeUrl());
        $this->assertSame('', $distribution->getTexts()->getPremiumUrl());
        $this->assertSame([], $distribution->getTexts()->getAuthors());
        $this->assertSame([], $distribution->getTexts()->getKeywords());
        $this->assertSame(6978, $distribution->getTexts()->getRubricId());
    }

    private function validatePremiumDistribution(ArtemisAudioDistribution $distribution): void
    {
        $this->assertSame('http://audio.smedata.localhost/7994f48d-118e-4dc6-8245-98b546cda6dc/789-the-runaround.mp3', $distribution->getTexts()->getPremiumUrl());
    }
}