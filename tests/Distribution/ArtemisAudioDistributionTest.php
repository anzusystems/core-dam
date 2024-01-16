<?php

declare(strict_types=1);


namespace App\Tests\Distribution;

use AnzuSystems\CoreDamBundle\DataFixtures\AudioFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PodcastFixtures;
use AnzuSystems\CoreDamBundle\Domain\Audio\AudioPositionFacade;
use AnzuSystems\CoreDamBundle\Domain\Podcast\PodcastRssReader;
use AnzuSystems\CoreDamBundle\Domain\Podcast\RssImportManager;
use AnzuSystems\CoreDamBundle\Domain\PodcastEpisode\EpisodeRssImportManager;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\HttpClient\RssClient;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Domain\ArtemisAudioDistribution\ArtemisAudioDistributionAutomat;
use App\Entity\ArtemisAudioDistribution;
use App\Tests\Controller\AbstractController;

final class ArtemisAudioDistributionTest extends AbstractController
{
    private RssImportManager $importManager;
    private AudioPositionFacade $audioPositionFacade;
    private ArtemisAudioDistributionAutomat $automat;
    private EpisodeRssImportManager $episodeRssImportManager;
    private PodcastRssReader $reader;
    private RssClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importManager = $this->getService(RssImportManager::class);
        $this->audioPositionFacade = $this->getService(AudioPositionFacade::class);
        $this->automat = $this->getService(ArtemisAudioDistributionAutomat::class);
        $this->reader = $this->getService(PodcastRssReader::class);
        $this->client = $this->getService(RssClient::class);
        $this->episodeRssImportManager = $this->getService(EpisodeRssImportManager::class);
    }

    public function testMakePublicUrl(): void
    {
        $audioFile = $this->entityManager->find(AudioFile::class, AudioFixtures::AUDIO_ID_1);

        $this->automat->makeAudioPublicUrl($audioFile);
        $this->assertNull($audioFile->getMainRoute());

        $this->audioPositionFacade->setToSlot($audioFile->getAsset(), $audioFile, 'premium');
        $this->automat->makeAudioPublicUrl($audioFile);
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
        $this->syncPodcast($episode->getPodcast());

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
        $this->syncPodcast($podcast);

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

    /**
     * @throws SerializerException
     */
    private function syncPodcast(Podcast $podcast): void
    {
        $this->reader->initReader($this->client->readPodcastRss($podcast));
        $this->importManager->syncPodcast($podcast, $this->reader->readChannel());

        foreach ($this->reader->readItems() as $podcastItem) {
            $this->episodeRssImportManager->importEpisode($podcast, $podcastItem);
        }
    }
}