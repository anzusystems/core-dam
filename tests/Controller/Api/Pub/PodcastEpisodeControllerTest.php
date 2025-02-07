<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Pub;

use AnzuSystems\CoreDamBundle\DataFixtures\PodcastEpisodeFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PodcastFixtures;
use App\Tests\Controller\Api\AbstractApiController;
use App\Tests\data\Model\ApiClientFirewall;
use Symfony\Component\HttpFoundation\Response;

final class PodcastEpisodeControllerTest extends AbstractApiController
{
    /**
     * @dataProvider getOneDataProvider
     */
    public function testGetOne(
        string $publicExportSlug,
        string $podcastEpisodeId,
        array $expectedData,
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/podcast-episodes/$podcastEpisodeId";
        $response = $client->get($url);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->assertArrayHasKey('publicationDate', $json);
        $this->assertArrayHasKey('asset', $json);
        unset($json['publicationDate']);
        unset($json['asset']);
        $this->assertEqualsCanonicalizing($expectedData, $json);

        $this->assertCacheHeaders($response);
    }

    public function getOneDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'podcastEpisodeId' => PodcastEpisodeFixtures::EPISODE_1_ID,
                'expectedData' => [
                    'id' => PodcastEpisodeFixtures::EPISODE_1_ID,
                    'podcast' => PodcastFixtures::PODCAST_1,
                    'title' => 'Episode 1',
                    'description' => 'Episode 1 description',
                    'duration' => 0,
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
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider getListDataProviderByPodcast
     */
    public function testGetListByPodcast(
        string $publicExportSlug,
        string $podcast,
        array $expectedPodcastList,
        array $pubApiParams = [],
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/podcasts/$podcast/podcast-episodes?" . http_build_query($pubApiParams);
        $response = $client->get($url);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertCacheHeaders($response);

        $this->assertCount(count($expectedPodcastList), $json['data']);
        foreach ($expectedPodcastList as $index => $expectedPodcast) {
            $this->assertArrayHasKey($index, $json['data']);
            $this->assertEquals($expectedPodcast['id'], $json['data'][$index]['id']);
        }
    }

    public function getListDataProviderByPodcast(): array
    {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'podcast' => PodcastFixtures::PODCAST_1,
                'expectedPodcastList' => [
                    [
                        'id' => PodcastEpisodeFixtures::EPISODE_1_ID,
                    ],
                ]
            ],
            [
                'publicExportSlug' => 'cms-web',
                'podcast' => PodcastFixtures::PODCAST_2,
                'expectedPodcastList' => [
                ]
            ],
        ];
    }

    /**
     * @dataProvider getListNotFoundDataProvider
     */
    public function testGetListNotFound(
        string $publicExportSlug,
        string $podcast,
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $response = $client->get("/api/pub/$publicExportSlug/podcasts/$podcast/podcast-episodes");
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertCacheHeaders($response);
    }

    public function getListNotFoundDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'zofia',
                'podcast' => PodcastFixtures::PODCAST_1,
            ],
        ];
    }

    /**
     * @dataProvider getListDataProvider
     */
    public function testGetList(
        string $publicExportSlug,
        string $podcast,
        array $expectedPodcastList,
        array $pubApiParams = [],
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/podcast-episodes?" . http_build_query($pubApiParams);
        $response = $client->get($url);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertCacheHeaders($response);

        $this->assertCount(count($expectedPodcastList), $json['data']);
        foreach ($expectedPodcastList as $index => $expectedPodcast) {
            $this->assertArrayHasKey($index, $json['data']);
            $this->assertEquals($expectedPodcast['id'], $json['data'][$index]['id']);
        }
    }

    public function getListDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'podcast' => PodcastFixtures::PODCAST_1,
                'expectedPodcastList' => [
                    [
                        'id' => PodcastEpisodeFixtures::EPISODE_1_ID,
                    ]
                ]
            ],
        ];
    }
}
