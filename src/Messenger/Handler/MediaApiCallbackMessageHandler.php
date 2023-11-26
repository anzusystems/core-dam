<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Exception\MediaApiClientException;
use App\HttpClient\MediaApiClient;
use App\Messenger\Message\MediaApiCallbackMessage;
use JsonException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class MediaApiCallbackMessageHandler
{
    public function __construct(
        private ImageFileRepository $imageFileRepository,
        private MediaApiClient $mediaApiClient,
    ) {
    }

    /**
     * @throws SerializerException
     * @throws MediaApiClientException
     * @throws JsonException
     */
    public function __invoke(MediaApiCallbackMessage $message): void
    {
        $imageFile = $this->imageFileRepository->find($message->getImageId());

        if ($imageFile) {
            $this->mediaApiClient->sendImageChangeState($imageFile);
        }
    }
}
