<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;


use AnzuSystems\SerializerBundle\Serializer;
use App\Tests\ApiClient;
use App\Tests\Controller\AbstractController;
use App\Tests\data\Model\ApiClientFirewall;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController extends AbstractController
{
    protected Serializer $serializer;
    protected static KernelBrowser $client;

    /** @psalm-var array<string|int, ApiClient> */
    private array $clients = [];

    public function getApiClient(?int $userId = null, ApiClientFirewall $firewall = ApiClientFirewall::Admin): ApiClient
    {
        $key = $userId ?? 'anonymous';
        if (false === isset($this->clients[$key])) {
            $this->clients[$key] = new ApiClient(
                client: static::$client,
                serializer: $this->serializer,
                userId: $userId,
                firewall: $firewall,
            );
        }
        return $this->clients[$key];
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::$client = $this->getService('test.client');
        static::$client->disableReboot();

        $this->serializer = $this->getService(Serializer::class);
    }

    protected function assertListResponse(array $json, ?int $expectedItemsCount = null): void
    {
        $this->assertArrayHasKey('totalCount', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);
        if (is_int($expectedItemsCount)) {
            $this->assertCount($expectedItemsCount, $json['data']);
        }
    }

    protected function assertResponseAndGetJsonContent(Response $response, int $expectedStatusCode = Response::HTTP_OK): array
    {
        $this->assertSame($expectedStatusCode, $response->getStatusCode());
        $this->assertJson($response->getContent());

        return json_decode($response->getContent(), true);
    }
}
