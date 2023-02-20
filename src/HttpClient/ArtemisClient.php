<?php

declare(strict_types=1);

namespace App\HttpClient;

use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Exception\DistributionFailedException;
use AnzuSystems\CoreDamBundle\Logger\DamLogger;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionFailReason;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
//use App\Exception\UnrecoverableArtemisDistributionException;
use App\Model\Dto\Artemis\ArtemisAudioMediaResponseDto;
use App\Model\Dto\Artemis\ArtemisMediaDto;
use App\Model\Dto\Artemis\ArtemisRubricDto;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class ArtemisClient
{
    use SerializerAwareTrait;

    public function __construct(
        private readonly HttpClientInterface $artemisApiClient,
        private readonly DamLogger $logger,
    ) {
    }

    /**
     * @return list<ArtemisRubricDto>
     *
     * @throws SerializerException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getRubricsBySectionId(int $sectionId): array
    {
        $response = $this->artemisApiClient->request(
            Request::METHOD_GET,
            sprintf(
                '/api/rest/v1/rubrics/%s',
                $sectionId
            )
        );

        /** @var list<ArtemisRubricDto> $artemisRubrics */
        $artemisRubrics = $this->serializer->deserializeIterable($response->getContent(), ArtemisRubricDto::class, []);

        return $artemisRubrics;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws SerializerException
     */
    public function updateMedia(string $mediaId, ArtemisMediaDto $mediaDto): ArtemisAudioMediaResponseDto
    {
        return $this->mediaRequest(Request::METHOD_PATCH, "/api/rest/v1/media/{$mediaId}", $mediaDto);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws SerializerException
     */
    public function createMedia(ArtemisMediaDto $mediaDto): ArtemisAudioMediaResponseDto
    {
        return $this->mediaRequest(Request::METHOD_POST, '/api/rest/v1/media', $mediaDto);
    }

    /**
     * @throws SerializerException
     * @throws TransportExceptionInterface
     */
    private function mediaRequest(string $method, string $url, ArtemisMediaDto $mediaDto): ArtemisAudioMediaResponseDto
    {
        try {
            $response = $this->artemisApiClient->request(
                $method,
                $url,
                [
                    'json' => $this->serializer->toArray($mediaDto),
                ]
            );

            return $this->serializer->deserialize($response->getContent(), ArtemisAudioMediaResponseDto::class);
        } catch (ServerException $exception) {
            $this->logger->error(
                DamLogger::NAMESPACE_DISTRIBUTION,
                sprintf('Artemis distribute failed (%s)', $exception->getMessage())
            );

            throw new DistributionFailedException(DistributionFailReason::Unknown);
        } catch (HttpExceptionInterface $exception) {
            $this->logger->error(
                DamLogger::NAMESPACE_DISTRIBUTION,
                sprintf('Artemis distribute validation failed (%s)', $exception->getMessage())
            );

            throw new DistributionFailedException(DistributionFailReason::ValidationFailed);
        } catch (Throwable $exception) {
            $this->logger->error(
                DamLogger::NAMESPACE_DISTRIBUTION,
                sprintf('Artemis distribute unexpected error (%s)', $exception->getMessage())
            );

            throw new DistributionFailedException(DistributionFailReason::Unknown);
        }
    }
}
