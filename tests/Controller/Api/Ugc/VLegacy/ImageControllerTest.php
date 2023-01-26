<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Ugc\VLegacy;

use App\DataFixtures\ImageFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\Controller\Api\AbstractApiControllerTest;
use Symfony\Component\HttpFoundation\Response;

final class ImageControllerTest extends AbstractApiControllerTest
{
    public function testSearchList()
    {
        // 1. Basic list
        $client = $this->getClient(UserFixtures::ID_USER_TWO, true);
        $response = $client->get('/api/ugc/vlegacy/blog/1/image');
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertListResponse($json, 2);
        $this->assertSame($json['data'][0]['id'], ImageFixtures::IMAGE_2_ID);
        $this->assertSame($json['data'][0]['texts']['description'], ImageFixtures::IMAGE_2_DESCRIPTION);
        $this->assertSame($json['data'][0]['authors'][0]['customAuthor'], ImageFixtures::IMAGE_2_AUTHOR);
        $this->assertSame($json['data'][1]['id'], ImageFixtures::IMAGE_1_ID);
        $this->assertSame($json['data'][1]['texts']['description'], ImageFixtures::IMAGE_1_DESCRIPTION);
        $this->assertSame($json['data'][1]['authors'][0]['customAuthor'], ImageFixtures::IMAGE_1_AUTHOR);

        // 2. List by ID
        $response = $client->get('/api/ugc/vlegacy/blog/1/image', ['filter_in' => ['id' => ImageFixtures::IMAGE_1_ID]]);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertListResponse($json, 1);
        $this->assertSame($json['data'][0]['id'], ImageFixtures::IMAGE_1_ID);
    }

    private function assertListResponse(array $json, ?int $expectedItemsCount = null): void
    {
        $this->assertArrayHasKey('totalCount', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);
        if (is_int($expectedItemsCount)) {
            $this->assertCount($expectedItemsCount, $json['data']);
        }
    }

    private function assertResponseAndGetJsonContent(Response $response, int $expectedStatusCode = Response::HTTP_OK): array
    {
        $this->assertSame($expectedStatusCode, $response->getStatusCode());
        $this->assertJson($response->getContent());

        return json_decode($response->getContent(), true);
    }
}
