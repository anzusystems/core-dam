<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Pub;

use AnzuSystems\CoreDamBundle\DataFixtures\AudioFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PodcastEpisodeFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PodcastFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\VideoFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\VideoShowEpisodeFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\VideoShowFixtures;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use App\Tests\Controller\Api\AbstractApiController;
use App\Tests\data\Model\ApiClientFirewall;
use PHPUnit\Framework\Attributes\DataProvider;

final class AssetControllerTest extends AbstractApiController
{
    #[DataProvider('getOneVideoAssetDataProvider')]
    public function testGetOneVideoAsset(
        string $publicExportSlug,
        string $audioFileId,
        array $expectedData,
    ): void {
        $videoFile = $this->entityManager->find(VideoFile::class, $audioFileId);
        $assetId = $videoFile->getAsset()->getId();
        $expectedData['id'] = $assetId;

        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/assets/$assetId";
        $response = $client->get($url);

        $imageId = $videoFile->getImagePreview()->getImageFile()->getId();
        $expectedData['thumbnail']['links'] = array_map(
            fn (array $link): array => array_merge($link, ['url' => sprintf($link['url'], $imageId)]),
            $expectedData['thumbnail']['links']
        );

        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertArrayHasKey('videoShow', $json);
        $this->assertArrayHasKey('publicationDate', $json['videoShow']);
        unset($json['videoShow']['publicationDate']);

        $this->assertEqualsCanonicalizing($expectedData, $json);
    }

    public static function getOneVideoAssetDataProvider(): array {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'audioFileId' => VideoFixtures::VIDEO_ID_1,
                'expectedData' => [
                    'id' => VideoFixtures::VIDEO_ID_1,
                    'title' => 'Video title',
                    'type' => 'video',
                    'videoShow' => [
                        'id' => VideoShowFixtures::SHOW_1,
                        'title' => 'Rozhovory ZKH',
                        'episodeTitle' => 'Episode with asset',
                        'episode' => VideoShowEpisodeFixtures::EPISODE_1,
                    ],
                    'duration' => 0,
                    'distributions' => [
                        [
                            'type' => 'youtube',
                            'id' => '8ZMm3wUrZSY',
                            'fallbackUrl' => 'https://www.youtube.com/watch?v=8ZMm3wUrZSY'
                        ],
                        [
                            'type' => 'jwVideo',
                            'id' => '1HssSBsu',
                            'directUrl' => '',
                            'fallbackUrl' => 'https://cdn.jwplayer.com/players/1HssSBsu.html'
                        ]
                    ],
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
                ],
            ],
        ];
    }

    #[DataProvider('getOneAudioAssetDataProvider')]
    public function testGetOneAudioAsset(
        string $publicExportSlug,
        string $audioFileId,
        array $expectedData,
    ): void {
        $assetId = $this->entityManager->find(AudioFile::class, $audioFileId)->getAsset()->getId();
        $expectedData['id'] = $assetId;

        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/assets/$assetId";
        $response = $client->get($url);

        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertArrayHasKey('podcast', $json);
        $this->assertArrayHasKey('publicationDate', $json['podcast']);
        unset($json['podcast']['publicationDate']);

        $this->assertEqualsCanonicalizing($expectedData, $json);
    }

    public static function getOneAudioAssetDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'audioFileId' => AudioFixtures::AUDIO_ID_1,
                'expectedData' => [
                    'id' => AudioFixtures::AUDIO_ID_1,
                    'title' => '783: Kids These Days',
                    'type' => 'audio',
                    'podcast' => [
                        'id' => PodcastFixtures::PODCAST_1,
                        'title' => 'Dobré ráno',
                        'description' => '',
                        'episode' => PodcastEpisodeFixtures::EPISODE_1_ID,
                        'episodeTitle' => 'Episode 1',
                        'episodeDescription' => 'Episode 1 description',
                    ],
                    'media' => [
                        [
                            'type' => 'free',
                            'linkUrl' => 'http://core.dam.localhost/rssurl',
                            'duration' => 1
                        ]
                    ],
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
            ],
        ];
    }
}
