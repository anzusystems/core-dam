<?php

declare(strict_types=1);

namespace App\HttpClient;

use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Exception\UnrecoverableArtemisDistributionException;
use App\Model\DTO\Artemis\ArtemisMediaDto;
use App\Model\DTO\Artemis\ArtemisMediaResponseDto;
use App\Model\Dto\Artemis\ArtemisRubricDto;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ArtemisClient
{
    use SerializerAwareTrait;

    public function __construct(
        private readonly HttpClientInterface $artemisApiClient,
    ) {
    }

    public function getRubricsBySectionId(int $sectionId): array
    {
        try {
            $response = $this->artemisApiClient->request(
                Request::METHOD_GET,
                sprintf(
                    '/api/rest/v1/rubrics/%s',
                    $sectionId
                )
            );

            return $this->serializer->deserializeIterable($response->getContent(), ArtemisRubricDto::class, []);
        } catch (HttpExceptionInterface $exception) {
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws SerializerException
     */
    public function createMedia(ArtemisMediaDto $mediaDto)
    {
        dump($this->serializer->serialize($mediaDto));
        try {
            $response = $this->artemisApiClient->request(
                Request::METHOD_POST,
                '/api/rest/v1/media',
                [
                    'json' => $this->serializer->toArray($mediaDto),
                ]
            );

            return $response->getContent();
        } catch (ServerException $exception) {
            dump($response->getStatusCode());
            dump($exception->getMessage());
//            throw $this->artemisExceptionHelper->artemisEndPointFailed(
//                $this->artemisMediaDtoFactory->serializeMediaDtoToJson($mediaDto),
//                $exception
//            );
        } catch (HttpExceptionInterface $exception) {

            dump($response->getStatusCode());
            dump($exception->getMessage());
//            dump($exception->getResponse()->getContent(false));

//            $artemisError = $this->artemisMediaDtoFactory->deserializeArtemisErrorDto($exception->getResponse()->getContent(false));
//
//            throw $this->artemisExceptionHelper->failedToDistributeMediaToArtemis(
//                $artemisError->getTitle(),
//                $exception
//            );
        }
    }
}
