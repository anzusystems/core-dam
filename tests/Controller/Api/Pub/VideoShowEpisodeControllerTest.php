<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Pub;

use AnzuSystems\CoreDamBundle\DataFixtures\PodcastEpisodeFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PodcastFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\VideoShowEpisodeFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\VideoShowFixtures;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Entity\VideoShow;
use AnzuSystems\CoreDamBundle\Entity\VideoShowEpisode;
use App\Tests\Controller\Api\AbstractApiController;
use App\Tests\data\Model\ApiClientFirewall;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class VideoShowEpisodeControllerTest extends AbstractApiController
{
    #[DataProvider('getOneDataProvider')]
    public function testGetOne(
        string $publicExportSlug,
        string $videoShowEpisodeId,
        array $expectedData,
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);
        $url = "/api/pub/$publicExportSlug/video-show-episodes/$videoShowEpisodeId";
        $response = $client->get($url);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->assertArrayHasKey('asset', $json);
        $asset = $this->entityManager->find(Asset::class, $json['asset']);
        /** @var VideoFile $videoFile */
        $videoFile = $asset->getMainFile();
        unset($json['asset']);
        $this->assertArrayHasKey('publicationDate', $json);
        unset($json['publicationDate']);

        $imageId = $videoFile->getImagePreview()->getImageFile()->getId();
        $expectedData['thumbnail']['links'] = array_map(
            fn (array $link): array => array_merge($link, ['url' => sprintf($link['url'], $imageId)]),
            $expectedData['thumbnail']['links']
        );

        $this->assertEqualsCanonicalizing($expectedData, $json);

        $this->assertCacheHeaders($response);
    }

    public static function getOneDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'videoShowEpisodeId' => VideoShowEpisodeFixtures::EPISODE_1,
                'expectedData' => [
                    'id' => VideoShowEpisodeFixtures::EPISODE_1,
                    'videoShow' => VideoShowFixtures::SHOW_1,
                    'title' => 'Episode with asset',
                    'duration' => 0,
                    'thumbnail' => [
                        'links' => [
                            [
                                'type' => 'image',
                                'url' => 'http://image.smedata.localhost/image/w2000-h0-c0/%s.jpg',
                                'requestedWidth' => 2000,
                                'requestedHeight' => 0,
                                'title' => 'large'
                            ],
                            [
                                'type' => 'image',
                                'url' => 'http://image.smedata.localhost/image/w1000-h563-c0/%s.jpg',
                                'requestedWidth' => 1000,
                                'requestedHeight' => 563,
                                'title' => 'medium'
                            ],
                            [
                                'type' => 'image',
                                'url' => 'http://image.smedata.localhost/image/w614-h345-c0/%s.jpg',
                                'requestedWidth' => 614,
                                'requestedHeight' => 345,
                                'title' => 'small'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    #[DataProvider('getListDataProvider')]
    public function testGetList(
        string $publicExportSlug,
        string $videoShow,
        array $expectedPodcastList,
        array $pubApiParams = [],
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/video-shows/$videoShow/video-show-episodes?" . http_build_query($pubApiParams);
        $response = $client->get($url);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertCacheHeaders($response);

        $this->assertCount(count($expectedPodcastList), $json['data']);
        foreach ($expectedPodcastList as $index => $expectedPodcast) {
            $this->assertArrayHasKey($index, $json['data']);
            $this->assertEquals($expectedPodcast['id'], $json['data'][$index]['id']);
        }
    }

    public static function getListDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'videoShow' => VideoShowFixtures::SHOW_1,
                'expectedPodcastList' => [
                    [
                        'id' => VideoShowEpisodeFixtures::EPISODE_1,
                    ],
                ]
            ],
        ];
    }

    #[DataProvider('getListNotFoundDataProvider')]
    public function testGetListNotFound(
        string $publicExportSlug,
        string $videoShow,
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $response = $client->get("/api/pub/$publicExportSlug/video-shows/$videoShow/video-show-episodes");
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertCacheHeaders($response);
    }

    public static function getListNotFoundDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'zofia',
                'videoShow' => PodcastFixtures::PODCAST_1,
            ],
        ];
    }
}
