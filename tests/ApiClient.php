<?php
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Tests;

use AnzuSystems\AuthBundle\Util\JwtUtil;
use AnzuSystems\CommonBundle\ApiFilter\ApiResponseList;
use AnzuSystems\Contracts\Exception\AnzuException;
use AnzuSystems\SerializerBundle\Serializer;
use App\Entity\User;
use App\Security\Util\JwtUgcUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @template T
 */
final readonly class ApiClient
{
    public function __construct(
        private KernelBrowser $client,
        private Serializer $serializer,
        private ?int $userId = null,
        private bool $ugcApi = false,
    ) {
    }

    public function get(string $uri, array $queryParams = []): Response
    {
        return $this->request(Request::METHOD_GET, $uri, attributes: $queryParams);
    }

    public function delete(string $uri): Response
    {
        return $this->request(Request::METHOD_DELETE, $uri);
    }

    public function post(string $uri, array $jsonBody = []): Response
    {
        return $this->request(Request::METHOD_POST, $uri, json_encode($jsonBody));
    }

    public function postChunkFile(string $uri, UploadedFile $file, array $attributes = []): Response
    {
        return $this->request(
            method: Request::METHOD_POST,
            uri: $uri,
            attributes: [
                'chunk' => json_encode($attributes)
            ],
            files: [
                'file' => $file
            ]
        );
    }

    public function patch(string $uri, array $jsonBody = []): Response
    {
        return $this->request(Request::METHOD_PATCH, $uri,  json_encode($jsonBody));
    }

    public function put(string $uri, array $jsonBody = []): Response
    {
        return $this->request(Request::METHOD_PUT, $uri, json_encode($jsonBody));
    }

    /**
     * @param class-string<T> $className
     *
     * @return T
     */
    public function deserializeResponse(Response $response, string $className): object
    {
        return $this->serializer->deserialize($response->getContent(), $className);
    }

    /**
     * @param class-string<T> $className
     *
     * @return iterable<int, T>
     */
    public function deserializeApiResponseList(Response $response, string $className): iterable
    {
        $apiResponseList = $this->serializer->deserialize($response->getContent(), ApiResponseList::class);

        return $this->serializer->fromArrayToIterable($apiResponseList->getData(), $className, []);
    }

    /**
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function deserializeFirstFromList(Response $response, string $className): object|null
    {
        $list = $this->deserializeApiResponseList($response, $className);

        return $list[0] ?? null;
    }

    private function request(string $method, string $uri, string $body = '', array $attributes = [], array $files = []): Response
    {
        $token = $this->getToken();

        $this->client->request(
            method: $method,
            uri: $uri,
            parameters: $attributes,
            files: $files,
            server: [
                'CONTENT_TYPE' => 'application/json',
            ] + ($token ? [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ] : [
            ]),
            content: $body,
        );

        return $this->client->getResponse();
    }

    private function getToken(): ?string
    {
        if (null === $this->userId) {
            return null;
        }

        /** @var User|null $user */
        $user = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->find(User::class, $this->userId);

        if (null === $user) {
            throw new AnzuException(sprintf('User (%d) not found!', $this->userId));
        }

        if ($this->ugcApi) {
            return $this->client->getContainer()->get(JwtUgcUtil::class)->createForUser($user)->toString();
        }

        return $this->client->getContainer()->get(JwtUtil::class)->create($user->getAuthId())->toString();
    }
}
