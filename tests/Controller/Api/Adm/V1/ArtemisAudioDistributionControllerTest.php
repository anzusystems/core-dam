<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Adm\V1;

use AnzuSystems\CoreDamBundle\DataFixtures\AudioFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\ImageFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PodcastFixtures;
use AnzuSystems\CoreDamBundle\Domain\AssetSlot\AssetSlotFactory;
use AnzuSystems\CoreDamBundle\Domain\PodcastEpisode\PodcastEpisodeFactory;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use App\App;
use App\Distribution\Modules\Factory\ArtemisAudioDtoFactory;
use App\Entity\ArtemisAudioDistribution;
use App\Model\Dto\Artemis\ArtemisMediaAuthorDto;
use App\Model\Dto\Artemis\ArtemisMediaTagDto;
use App\Tests\Controller\Api\AbstractApiControllerTest;
use Symfony\Component\HttpFoundation\Response;

final class ArtemisAudioDistributionControllerTest extends AbstractApiControllerTest
{
    private const TEST_CUSTOM_DATA = [
        'title' => '783: Kids These Days',
        'description' => 'Custom audio description',
        'keywords' => ['News', 'Podcast', 'Politics'],
        'authors' => ['Aarne Ormonde', 'Larry Queen', 'Malka Raisa'],
        'freeUrl' => 'https://audio.sme.sk/rssurl',
        'premiumUrl' => 'http://audio.smedata.localhost/public-path',
        'createArticle' => false,
        'extRssId' => '123',
        'rubricId' => 6978,
        'podcastId' => PodcastFixtures::PODCAST_1
    ];

    private AssetSlotFactory $assetSlotFactory;
    private PodcastEpisodeFactory $podcastEpisodeFactory;
    private ArtemisAudioDtoFactory $artemisAudioDtoFactory;
    private AudioFile $audioFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetSlotFactory = $this->getService(AssetSlotFactory::class);
        $this->podcastEpisodeFactory = $this->getService(PodcastEpisodeFactory::class);
        $this->artemisAudioDtoFactory = $this->getService(ArtemisAudioDtoFactory::class);
        $this->setupAudioData();
    }

    public function testPreparePayload(): void
    {
        $client = $this->getClient(App::getUserIdAdmin());
        $response = $client->get(sprintf(
            '/api/adm/v1/custom-distribution/asset-file/%s/prepare-payload/%s',
            AudioFixtures::AUDIO_ID_1,
                'artemis_podcast_cms'
            )
        );
        $data = json_decode($response->getContent(), true);

        $this->assertEqualsCanonicalizing(self::TEST_CUSTOM_DATA, $data['customData']);
    }

    // todo
//    public function testDistributeSuccess(): void
//    {
//        $client = $this->getClient(App::getUserIdAdmin());
//
//        $response = $client->post(
//            sprintf(
//                '/api/adm/v1/custom-distribution/asset-file/%s/distribute',
//                AudioFixtures::AUDIO_ID_1,
//            ),
//            [
//                'distributionService' => 'artemis_podcast_cms',
//                'customData' => self::TEST_CUSTOM_DATA
//            ]
//        );
//
//        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
//        $data = json_decode($response->getContent(), true);
//        $this->assertSame(self::TEST_CUSTOM_DATA, $data['customData']);
//        /** @var ArtemisAudioDistribution $distribution */
//        $distribution = $this->entityManager->getRepository(ArtemisAudioDistribution::class)->find($data['id']);
//        $this->assertNotNull($distribution);
//
//        $this->assertSame(self::TEST_CUSTOM_DATA['title'], $distribution->getTexts()->getTitle());
//        $this->assertSame(self::TEST_CUSTOM_DATA['description'], $distribution->getTexts()->getDescription());
//        $this->assertSame(self::TEST_CUSTOM_DATA['keywords'], $distribution->getTexts()->getKeywords());
//        $this->assertSame(self::TEST_CUSTOM_DATA['authors'], $distribution->getTexts()->getAuthors());
//        $this->assertSame(self::TEST_CUSTOM_DATA['freeUrl'], $distribution->getTexts()->getFreeUrl());
//        $this->assertSame(self::TEST_CUSTOM_DATA['premiumUrl'], $distribution->getTexts()->getPremiumUrl());
//        $this->assertSame(self::TEST_CUSTOM_DATA['createArticle'], $distribution->getFlags()->isCreateArticle());
//        $this->assertSame(self::TEST_CUSTOM_DATA['extRssId'], $distribution->getTexts()->getExtRssId());
//        $this->assertSame(self::TEST_CUSTOM_DATA['rubricId'], $distribution->getTexts()->getRubricId());
//        $this->assertSame(self::TEST_CUSTOM_DATA['podcastId'], $distribution->getTexts()->getPodcastId());
//
//        $dto = $this->artemisAudioDtoFactory->createMediaDto($this->audioFile, $distribution);
//
//        $this->assertSame('http://admin-image.smedata.localhost/image/w1920-h0/'.ImageFixtures::IMAGE_ID_1_2.'.jpg', $dto->getImage()->getUrl());
//        $this->assertSame('Custom Data Title', $dto->getImage()->getTitle());
//
//        $this->assertSame(self::TEST_CUSTOM_DATA['title'], $dto->getTitle());
//        $this->assertSame(self::TEST_CUSTOM_DATA['description'], $dto->getDescription());
//        $this->assertSame(self::TEST_CUSTOM_DATA['rubricId'], $dto->getRubric()->getId());
//        $this->assertSame($this->audioFile->getAttributes()->getDuration(), $dto->getDuration());
//        $this->assertSame($this->audioFile->getAttributes()->getDuration(), $dto->getPremiumDirectSourceDuration());
//        $this->assertSame('audio', $dto->getType()->toString());
//        $this->assertSame(self::TEST_CUSTOM_DATA['createArticle'], $dto->isCreateArticle());
//        $this->assertSame(
//            self::TEST_CUSTOM_DATA['authors'],
//            array_map(fn (ArtemisMediaAuthorDto $author): string => $author->getFullName(), $dto->getAuthors())
//        );
//        $this->assertSame(
//            self::TEST_CUSTOM_DATA['keywords'],
//            array_map(fn (ArtemisMediaTagDto $tag): string => $tag->getTitle(), $dto->getTags())
//        );
//        $this->assertSame(self::TEST_CUSTOM_DATA['freeUrl'], $dto->getDirectSourceUrl());
//        $this->assertSame(self::TEST_CUSTOM_DATA['premiumUrl'], $dto->getPremiumSourceUrl());
//        $this->assertSame(self::TEST_CUSTOM_DATA['podcastId'], $dto->getMediaChannel()->getAnzuId());
//    }

    public function testDistributeFailed(): void
    {
        $client = $this->getClient(App::getUserIdAdmin());

        $response = $client->post(
            sprintf(
                '/api/adm/v1/custom-distribution/asset-file/%s/distribute',
                AudioFixtures::AUDIO_ID_1,
            ),
            [
                'distributionService' => 'artemis_podcast_cms',
                'customData' => []
            ]
        );
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertEqualsCanonicalizing(
            [
                'customData.title' => ['error_field_empty', 'error_field_length_min'],
                'customData.description' => ['error_field_empty', 'error_field_length_min'],
                'customData.rubricId' => ['error_field_empty'],
                'customData.keywords' => ['error_field_empty'],
                'customData.podcastId' => ['error_field_empty'],
                'customData.createArticle' => ['error_field_empty'],
            ],
            $data['fields'] ?? []
        );
    }

    private function setupAudioData(): void
    {
        $this->audioFile = $this->entityManager->getRepository(AudioFile::class)->find(AudioFixtures::AUDIO_ID_1);
        $this->assetSlotFactory->createRelation(
            asset: $this->audioFile->getAsset(),
            assetFile: $this->audioFile,
            slotName: 'paid',
            flush: false
        );
        $this->audioFile->getAudioPublicLink()
            ->setPublic(true)
            ->setSlug('public')
            ->setPath('public-path');
        $podcast = $this->entityManager->getRepository(Podcast::class)->find(PodcastFixtures::PODCAST_1);
        $episode = $this->podcastEpisodeFactory->createEpisodeWithAsset(
            asset: $this->audioFile->getAsset(),
            podcast: $podcast,
            flush: false
        );
        $episode->getAttributes()
            ->setRssUrl('https://audio.sme.sk/rssurl')
            ->setRssId('123');
        $this->entityManager->flush();
    }
}
