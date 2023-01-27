<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;


use AnzuSystems\SerializerBundle\Serializer;
use App\Tests\ApiClient;
use App\Tests\Controller\AbstractControllerTest;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiControllerTest extends AbstractControllerTest
{
    protected Serializer $serializer;
    protected static KernelBrowser $client;

    /** @psalm-var array<string|int, ApiClient> */
    private array $clients = [];

    public function getClient(?int $userId = null, bool $ugcApi = false): ApiClient
    {
        $key = $userId ?? 'anonymous';
        if (false === isset($this->clients[$key])) {
            $this->clients[$key] = new ApiClient(
                client: static::$client,
                serializer: $this->serializer,
                userId: $userId,
                ugcApi: $ugcApi,
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

    /**
     * @template T of object
     *
     * @param class-string<T>|string $id
     *
     * @return T
     */
    protected function getService(string $id): object
    {
        return static::getContainer()->get($id);
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

    protected function assertResponseAndGetJsonContent(Response $response): array
    {
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        return json_decode($response->getContent(), true);
    }
}
