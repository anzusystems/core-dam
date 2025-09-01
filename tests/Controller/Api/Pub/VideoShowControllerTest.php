<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Pub;

use AnzuSystems\CoreDamBundle\DataFixtures\PodcastFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\VideoShowFixtures;
use App\Tests\Controller\Api\AbstractApiController;
use App\Tests\data\Model\ApiClientFirewall;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class VideoShowControllerTest extends AbstractApiController
{
    #[DataProvider('getOneDataProvider')]
    public function testGetOne(
        string $publicExportSlug,
        string $videShowId,
        array $expectedData,
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/video-shows/$videShowId";
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
                'videShowId' => VideoShowFixtures::SHOW_1,
                'expectedData' => [
                    'id' =>  VideoShowFixtures::SHOW_1,
                    'title' => 'Rozhovory ZKH',
                ]
            ]
        ];
    }

    #[DataProvider('getListDataProvider')]
    public function testGetList(
        string $publicExportSlug,
        array $expectedList,
        array $pubApiParams = [],
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $url = "/api/pub/$publicExportSlug/video-shows?" . http_build_query($pubApiParams);
        $response = $client->get($url);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertCacheHeaders($response);

        $this->assertCount(count($expectedList), $json['data']);
        foreach ($expectedList as $index => $expectedData) {
            $this->assertArrayHasKey($index, $json['data']);
            $this->assertEquals($expectedData['id'], $json['data'][$index]['id']);
        }
    }

    public static function getListDataProvider(): array
    {
        return [
            [
                'publicExportSlug' => 'cms-web',
                'expectedList' => [
                    [
                        'id' =>  VideoShowFixtures::SHOW_1,
                        'title' => 'Rozhovory ZKH',
                    ],
                ]
            ],
        ];
    }

    #[DataProvider('getListNotFoundDataProvider')]
    public function testGetListNotFound(
        string $publicExportSlug,
    ): void {
        $client = $this->getApiClient(null, ApiClientFirewall::Pub);

        $response = $client->get("/api/pub/$publicExportSlug/video-shows");
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
}
