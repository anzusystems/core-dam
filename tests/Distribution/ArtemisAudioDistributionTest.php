<?php

declare(strict_types=1);


namespace App\Tests\Distribution;

use AnzuSystems\CoreDamBundle\DataFixtures\AudioFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PodcastFixtures;
use AnzuSystems\CoreDamBundle\Domain\Audio\AudioPositionFacade;
use AnzuSystems\CoreDamBundle\Domain\Podcast\PodcastManager;
use AnzuSystems\CoreDamBundle\Domain\Podcast\RssImportManager;
use AnzuSystems\CoreDamBundle\Domain\PodcastEpisode\PodcastEpisodeManager;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\Embeds\PodcastEpisodeTexts;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use App\Distribution\Modules\Factory\ArtemisAudioDtoFactory;
use App\Domain\ArtemisAudioDistribution\ArtemisAudioDistributionAutomat;
use App\Entity\ArtemisAudioDistribution;
use App\Tests\Controller\AbstractControllerTest;
use Doctrine\Common\Collections\ArrayCollection;
use Google\Service\SecurityCommandCenter\Pod;

final class ArtemisAudioDistributionTest extends AbstractControllerTest
{
    private RssImportManager $importManager;
    private AudioPositionFacade $audioPositionFacade;
    private ArtemisAudioDistributionAutomat $automat;
    private PodcastEpisodeManager $podcastEpisodeManager;
    private PodcastManager $podcastManager;
    private ArtemisAudioDtoFactory $artemisAudioDtoFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importManager = $this->getService(RssImportManager::class);
        $this->audioPositionFacade = $this->getService(AudioPositionFacade::class);
        $this->automat = $this->getService(ArtemisAudioDistributionAutomat::class);
        $this->podcastEpisodeManager = $this->getService(PodcastEpisodeManager::class);
        $this->podcastManager = $this->getService(PodcastManager::class);
        $this->artemisAudioDtoFactory = $this->getService(ArtemisAudioDtoFactory::class);
    }

    public function testMakePublicUrl(): void
    {
        $audioFile = $this->entityManager->find(AudioFile::class, AudioFixtures::AUDIO_ID_1);

        $this->automat->makeAudioPublicUrl($audioFile);
        $this->assertSame(false, $audioFile->getAudioPublicLink()->isPublic());

        $this->audioPositionFacade->setToSlot($audioFile->getAsset(), $audioFile, 'bonus');
        $this->automat->makeAudioPublicUrl($audioFile);
        $this->assertSame(true, $audioFile->getAudioPublicLink()->isPublic());

    }

    public function testBonusDistribution(): void
    {
        $podcast = $this->podcastManager->create((new Podcast()), false);
        $episode = (new PodcastEpisode())->setTexts((new PodcastEpisodeTexts())->setTitle('Bonus Test'));
        $episode->setPodcast($podcast);

        $this->podcastEpisodeManager->create($episode, false);

        $audioFile = $this->entityManager->getRepository(AudioFile::class)->find(AudioFixtures::AUDIO_ID_1);
        $this->audioPositionFacade->setToSlot($audioFile->getAsset(), $audioFile, 'bonus');
        $audioFile->getAsset()->setEpisodes(new ArrayCollection([$episode]));
        $episode->setAsset($audioFile->getAsset());

        $this->entityManager->flush();

        // manually triggers "processed" state
        $this->automat->makeAudioPublicUrl($audioFile);
        $this->automat->tryToDistribute($audioFile);

        $distribution = $this->entityManager->getRepository(ArtemisAudioDistribution::class)->findOneBy([
            'texts.podcastId' => $episode->getPodcast()->getId()
        ]);

        $this->assertNotNull($distribution);
        $this->assertSame('artemis_podcast_cms', $distribution->getDistributionService());
        $this->assertSame('artemis_podcast_cms', $distribution->getDistributionService());
        $this->assertSame('', $distribution->getTexts()->getFreeUrl());
        $this->assertSame('http://audio.smedata.localhost/7994f48d-118e-4dc6-8245-98b546cda6dc/783-kids-these-days.mp3', $distribution->getTexts()->getPremiumUrl());
    }

    public function testDistributionPreparedPremium(): void
    {
        $audioFile = $this->entityManager->getRepository(AudioFile::class)->find(AudioFixtures::AUDIO_ID_1);
        $episode=$audioFile->getAsset()->getEpisodes()->first();
        $episode->getTexts()->setTitle('Between them');

        $audioFile->getAsset()->getSlots()->map(
            fn (AssetSlot $slot): AssetSlot => $slot->setName('premium')
        );

        $this->automat->makeAudioPublicUrl($audioFile);
        $this->entityManager->flush();

        /** @var ArtemisAudioDistribution $distribution */
        $distribution = $this->entityManager->getRepository(ArtemisAudioDistribution::class)->findOneBy([
            'texts.episodeId' => $episode->getId()
        ]);
        $this->assertNull($distribution);
        $this->importManager->syncPodcast($episode->getPodcast());

        /** @var array<int, ArtemisAudioDistribution> $distributions */
        $distributions = $this->entityManager->getRepository(ArtemisAudioDistribution::class)->findBy([
            'texts.episodeId' => $episode->getId()
        ]);

        $this->assertCount(1, $distributions);
        /** @var ArtemisAudioDistribution $distribution */
        $distribution = $distributions[0];

        $this->assertTrue($distribution instanceof ArtemisAudioDistribution);
        $this->assertSame('783: Kids These Days', $distribution->getTexts()->getTitle());
        $this->assertSame('Custom audio description', $distribution->getTexts()->getDescription());
        $this->assertSame('45233 at https://www.thisamericanlife.org', $distribution->getTexts()->getExtRssId());
        $this->assertSame('http://core-dam.sme.localhost/download-file?file=audiob', $distribution->getTexts()->getFreeUrl());
        $this->assertSame('http://audio.smedata.localhost/7994f48d-118e-4dc6-8245-98b546cda6dc/783-kids-these-days.mp3', $distribution->getTexts()->getPremiumUrl());
    }

    public function testDistribution(): void
    {
        $podcast = $this->entityManager->getRepository(Podcast::class)->find(PodcastFixtures::PODCAST_1);

        // Run synchronization procedure for podcasts
        $this->importManager->syncPodcast($podcast);

        // Find RSS Episode created by RSS synchronization, validated preview Image
        $episode = $this->entityManager->getRepository(PodcastEpisode::class)->findOneBy([
            'podcast' => $podcast,
            'attributes.rssId' => '45232 at https://www.thisamericanlife.org'
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
        $this->assertSame('http://audio.smedata.localhost/7994f48d-118e-4dc6-8245-98b546cda6dc/789-the-runaround.mp3', $distribution->getTexts()->getPremiumUrl());
    }

    private function validateFreeDistribution(ArtemisAudioDistribution $distribution): void
    {
        $this->assertSame('artemis_podcast_cms', $distribution->getDistributionService());
        $this->assertSame('789: The Runaround', $distribution->getTexts()->getTitle());
        $this->assertSame('45232 at https://www.thisamericanlife.org', $distribution->getTexts()->getExtRssId());
        $this->assertSame('People being dodged, delayed, and evaded—and what they do to put an end to it.', $distribution->getTexts()->getDescription());
        $this->assertSame('http://core-dam.sme.localhost/download-file?file=audioa', $distribution->getTexts()->getFreeUrl());
        $this->assertSame('', $distribution->getTexts()->getPremiumUrl());
        $this->assertSame([], $distribution->getTexts()->getAuthors());
        $this->assertSame([], $distribution->getTexts()->getKeywords());
        $this->assertSame(6978, $distribution->getTexts()->getRubricId());
    }
}