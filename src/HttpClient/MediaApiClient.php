<?php

declare(strict_types=1);

namespace App\HttpClient;

use AnzuSystems\CommonBundle\Traits\LoggerAwareRequest;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Logger\DamLogger;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Exception\MediaApiClientException;
use App\Model\Domain\Asset\AssetFileMediaApiCallbackDecorator;
use JsonException;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MediaApiClient implements LoggerAwareInterface
{
    use LoggerAwareRequest;
    use SerializerAwareTrait;

    public function __construct(
        private readonly HttpClientInterface $mediaapiApiClient,
        private readonly DamLogger $damLogger,
    ) {
    }

    /**
     * @throws JsonException
     * @throws MediaApiClientException
     * @throws SerializerException
     */
    public function sendImageChangeState(ImageFile $imageFile): void
    {
        $response = $this->loggedRequest(
            client: $this->mediaapiApiClient,
            message: '[Mediaapi] image change state',
            url: '/api/v1/images/anzu-dam/' . (string) $imageFile->getId(),
            method: Request::METHOD_PATCH,
            json: $this->serializer->toArray(
                AssetFileMediaApiCallbackDecorator::getInstance($imageFile)
            ),
            timeout: 15,
        );

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return;
        }

        if ($response->hasClientError()) {
            $this->damLogger->error('Mediaapi', sprintf(
                'callback finished with status code (%s) for image file id (%s)',
                $response->getStatusCode(),
                (string) $imageFile->getId()
            ));

            return;
        }

        if ($response->hasError()) {
            throw MediaApiClientException::create($response->getContent());
        }
    }
}
