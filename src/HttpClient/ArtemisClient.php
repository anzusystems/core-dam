<?php

declare(strict_types=1);

namespace App\HttpClient;

use AnzuSystems\CommonBundle\Traits\LoggerAwareRequest;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Exception\DistributionFailedException;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionFailReason;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Model\Dto\Artemis\ArtemisMediaDto;
use App\Model\Dto\Artemis\ArtemisMediaResponseDto;
use JsonException;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ArtemisClient implements LoggerAwareInterface
{
    use LoggerAwareRequest;
    use SerializerAwareTrait;

    public function __construct(
        private readonly HttpClientInterface $artemisApiClient,
    ) {
    }

    /**
     * @throws SerializerException
     * @throws RuntimeException
     * @throws JsonException
     */
    public function updateMedia(string $mediaId, ArtemisMediaDto $mediaDto): ArtemisMediaResponseDto
    {
        return $this->mediaRequest(Request::METHOD_PUT, "/api/rest/v1/media/{$mediaId}", $mediaDto);
    }

    /**
     * @throws SerializerException
     * @throws RuntimeException
     * @throws JsonException
     */
    public function createMedia(ArtemisMediaDto $mediaDto): ArtemisMediaResponseDto
    {
        return $this->mediaRequest(Request::METHOD_POST, '/api/rest/v1/media', $mediaDto);
    }

    /**
     * @throws SerializerException
     * @throws RuntimeException
     * @throws JsonException
     */
    private function mediaRequest(string $method, string $url, ArtemisMediaDto $mediaDto): ArtemisMediaResponseDto
    {
        $response = $this->loggedRequest(
            client: $this->artemisApiClient,
            message: '[Artemis] Distribute',
            url: $url,
            method: $method,
            json: $this->serializer->toArray($mediaDto),
            timeout: 15,
        );

        if (
            Response::HTTP_BAD_REQUEST <= $response->getStatusCode() &&
            Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS >= $response->getStatusCode()
        ) {
            throw new DistributionFailedException(DistributionFailReason::ValidationFailed);
        }

        if ($response->hasError()) {
            throw new RuntimeException('Artemis distribution failed');
        }

        return $this->serializer->deserialize($response->getContent(), ArtemisMediaResponseDto::class);
    }
}
