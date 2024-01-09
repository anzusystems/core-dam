<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Adm\V1;

use AnzuSystems\CoreDamBundle\DataFixtures\VideoFixtures;
use AnzuSystems\CoreDamBundle\Domain\Distribution\DistributionManagerProvider;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Entity\YoutubeDistribution;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use App\App;
use App\Distribution\Modules\Factory\ArtemisVideoDtoFactory;
use App\Entity\ArtemisVideoDistribution;
use App\Model\Dto\Artemis\ArtemisMediaAuthorDto;
use App\Model\Dto\Artemis\ArtemisMediaTagDto;
use App\Tests\Controller\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Response;

final class ArtemisVideoDistributionControllerTest extends AbstractApiController
{
    private const TEST_CUSTOM_DATA = [
        'title' => 'Video title',
        'description' => 'Custom video description',
        'keywords' => ['News', 'Podcast', 'Politics'],
        'authors' => ['Aarne Ormonde', 'Larry Queen', 'Malka Raisa'],
        'createArticle' => false,
        'rubricId' => 7026,
    ];

    private DistributionManagerProvider $distributionManagerProvider;
    private ArtemisVideoDtoFactory $artemisVideoDtoFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->distributionManagerProvider = $this->getService(DistributionManagerProvider::class);
        $this->artemisVideoDtoFactory = $this->getService(ArtemisVideoDtoFactory::class);
    }

    public function testPreparePayload(): void
    {
        $client = $this->getApiClient(App::getUserIdAdmin());
        $response = $client->get(sprintf(
            '/api/adm/v1/custom-distribution/asset-file/%s/prepare-payload/%s',
            VideoFixtures::VIDEO_ID_1,
                'artemis_video_cms'
            )
        );
        $data = json_decode($response->getContent(), true);

        $this->assertEqualsCanonicalizing(self::TEST_CUSTOM_DATA, $data['customData']);
    }

    public function testDistributeSuccess(): void
    {
        $client = $this->getApiClient(App::getUserIdAdmin());

        $video = $this->entityManager->getRepository(VideoFile::class)->find(VideoFixtures::VIDEO_ID_1);
        $yt = $this->setupYtDistribution($video);
        $jw = $this->setupJwDistribution($video);

        $response = $client->post(
            sprintf(
                '/api/adm/v1/custom-distribution/asset-file/%s/distribute',
                VideoFixtures::VIDEO_ID_1,
            ),
            [
                'distributionService' => 'artemis_video_cms',
                'customData' => self::TEST_CUSTOM_DATA,
                'blockedBy' => [
                    $yt->getId(),
                    $jw->getId()
                ]
            ]
        );
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertSame(self::TEST_CUSTOM_DATA, $data['customData']);
        /** @var ArtemisVideoDistribution $distribution */
        $distribution = $this->entityManager->getRepository(ArtemisVideoDistribution::class)->find($data['id']);
        $this->assertNotNull($distribution);

        $this->assertSame(self::TEST_CUSTOM_DATA['title'], $distribution->getTexts()->getTitle());
        $this->assertSame(self::TEST_CUSTOM_DATA['description'], $distribution->getTexts()->getDescription());
        $this->assertSame(self::TEST_CUSTOM_DATA['keywords'], $distribution->getTexts()->getKeywords());
        $this->assertSame(self::TEST_CUSTOM_DATA['authors'], $distribution->getTexts()->getAuthors());
        $this->assertSame(self::TEST_CUSTOM_DATA['createArticle'], $distribution->getFlags()->isCreateArticle());

        $dto = $this->artemisVideoDtoFactory->createMediaDto($video, $distribution);
        $assetId = (string) $video->getImagePreview()?->getImageFile()?->getAsset()->getId();
        $imagePreviewId = (string) $video->getImagePreview()?->getImageFile()->getId();

        $this->assertSame("http://image-admin.smedata.localhost/image/w1920-h0/{$imagePreviewId}.jpg", $dto->getImage()->getUrl());
        $this->assertSame($assetId, $dto->getImage()->getTitle());
        $this->assertSame(self::TEST_CUSTOM_DATA['title'], $dto->getTitle());
        $this->assertSame(self::TEST_CUSTOM_DATA['description'], $dto->getDescription());
        $this->assertSame(self::TEST_CUSTOM_DATA['rubricId'], $dto->getRubric()->getId());
        $this->assertSame($video->getAttributes()->getDuration(), $dto->getDuration());
        $this->assertSame('video', $dto->getType());
        $this->assertSame('123YT', $dto->getYoutubeId());
        $this->assertSame('123JW', $dto->getJwId());
        $this->assertSame(self::TEST_CUSTOM_DATA['createArticle'], $dto->isCreateArticle());
        $this->assertSame(
            self::TEST_CUSTOM_DATA['authors'],
            array_map(fn (ArtemisMediaAuthorDto $author): string => $author->getFullName(), $dto->getAuthors())
        );
        $this->assertSame(
            self::TEST_CUSTOM_DATA['keywords'],
            array_map(fn (ArtemisMediaTagDto $tag): string => $tag->getTitle(), $dto->getTags())
        );
    }

    public function testDistributeFailed(): void
    {
        $client = $this->getApiClient(App::getUserIdAdmin());

        $response = $client->post(
            sprintf(
                '/api/adm/v1/custom-distribution/asset-file/%s/distribute',
                VideoFixtures::VIDEO_ID_1,
            ),
            [
                'distributionService' => 'artemis_video_cms',
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
                'customData.createArticle' => ['error_field_empty'],
            ],
            $data['fields'] ?? []
        );
    }

    private function setupYtDistribution(VideoFile $videoFile): YoutubeDistribution
    {
        $ytDistribution = (new YoutubeDistribution)
            ->setStatus(DistributionProcessStatus::Distributed)
            ->setExtId('123YT')
            ->setAssetId($videoFile->getAsset()->getId())
            ->setAssetFileId($videoFile->getId())
            ->setDistributionService('youtube_cms_main')
        ;

        return $this->distributionManagerProvider->get(YoutubeDistribution::class)->create($ytDistribution);
    }

    private function setupJwDistribution(VideoFile $videoFile): JwDistribution
    {
        $jwDistribution = (new JwDistribution)
            ->setStatus(DistributionProcessStatus::Distributed)
            ->setExtId('123JW')
            ->setAssetId($videoFile->getAsset()->getId())
            ->setAssetFileId($videoFile->getId())
            ->setDistributionService('jw_cms')
        ;

        return $this->distributionManagerProvider->get(YoutubeDistribution::class)->create($jwDistribution);
    }
}
