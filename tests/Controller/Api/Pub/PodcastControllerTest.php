<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Pub;

use AnzuSystems\CoreDamBundle\DataFixtures\PodcastFixtures;
use App\Tests\Controller\Api\AbstractApiController;
use App\Tests\data\Model\ApiClientFirewall;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class PodcastControllerTest extends AbstractApiController
{
    #[DataProvider('getOneDataProvider')]
    public function testGetOne(
        string $publicExportSlug,
        string $podcastId,
        array $expectedData,
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/podcasts/$podcastId";
        $response = $client->get($url);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedData, $json);
        $this->assertCacheHeaders($response);
    }

    public static function getOneDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'podcastId' => PodcastFixtures::PODCAST_1,
                'expectedData' => [
                    'id' => PodcastFixtures::PODCAST_1,
                    'title' => 'Dobré ráno',
                    'description' => '',
                    'rssUrl' => 'https://anchor.fm/s/8a651488/podcast/rss',
                    'thumbnail' => [
                        'links' => [
                            [
                                'type' => 'image',
                                'url' => 'http://image.smedata.localhost/image/w75-h75-c0/892d7b56-7423-4428-86a0-2d366685d823.jpg',
                                'requestedWidth' => 75,
                                'requestedHeight' => 75,
                                'title' => 'small'
                            ],
                            [
                                'type' => 'image',
                                'url' => 'http://image.smedata.localhost/image/w200-h200-c0/892d7b56-7423-4428-86a0-2d366685d823.jpg',
                                'requestedWidth' => 200,
                                'requestedHeight' => 200,
                                'title' => 'medium'
                            ],
                            [
                                'type' => 'image',
                                'url' => 'http://image.smedata.localhost/image/w300-h300-c0/892d7b56-7423-4428-86a0-2d366685d823.jpg',
                                'requestedWidth' => 300,
                                'requestedHeight' => 300,
                                'title' => 'large'
                            ]
                        ]
                    ],
                    'exportData' => []
                ]
            ]
        ];
    }

    #[DataProvider('getListDataProvider')]
    public function testGetList(
        string $publicExportSlug,
        array $expectedPodcastList,
        array $pubApiParams = [],
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/podcasts?" . http_build_query($pubApiParams);
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
                'expectedPodcastList' => [
                    [
                        'id' => PodcastFixtures::PODCAST_1,
                        'title' => 'Dobré ráno',
                    ],
                    [
                        'id' => PodcastFixtures::PODCAST_2,
                        'title' => 'Klik',
                    ],
                ]
            ],
            [
                'publicExportSlug' => 'cms-mobile',
                'expectedPodcastList' => [
                    [
                        'id' => PodcastFixtures::PODCAST_2,
                        'title' => 'Klik',
                    ],
                    [
                        'id' => PodcastFixtures::PODCAST_1,
                        'title' => 'Dobré ráno',
                    ],
                ]
            ],
            [
                'publicExportSlug' => 'cms-mobile',
                'expectedPodcastList' => [
                    [
                        'id' => PodcastFixtures::PODCAST_1,
                        'title' => 'Dobré ráno',
                    ],
                ],
                'pubApiParams' => ['excludeIds' => [PodcastFixtures::PODCAST_2]],
            ],
            [
                'publicExportSlug' => 'cms-web',
                'expectedPodcastList' => [
                ],
                'pubApiParams' => ['excludeIds' => [PodcastFixtures::PODCAST_2, PodcastFixtures::PODCAST_1]],
            ],
            [
                'publicExportSlug' => 'cms-web',
                'expectedPodcastList' => [
                ],
                'pubApiParams' => ['page' => 2],
            ],
        ];
    }

    #[DataProvider('getListNotFoundDataProvider')]
    public function testGetListNotFound(
        string $publicExportSlug,
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $response = $client->get("/api/pub/$publicExportSlug/podcasts");
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertCacheHeaders($response);
    }

    public static function getListNotFoundDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'zofia',
            ],
        ];
    }

    #[DataProvider('getListInvalidApiParamsDataProvider')]
    public function testListInvalidApiParams(
        string $publicExportSlug,
        array $pubApiParams = [],
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/podcasts?" . http_build_query($pubApiParams);
        $response = $client->get($url);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public static function getListInvalidApiParamsDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'pubApiParams' => ['excludeIds' => array_fill(0, 101, PodcastFixtures::PODCAST_1)],
            ],
            [
                'publicExportSlug' => 'cms-web',
                'pubApiParams' => ['page' => 120],
            ],
            [
                'publicExportSlug' => 'cms-web',
                'pubApiParams' => ['limit' => 120],
            ],
        ];
    }
}
