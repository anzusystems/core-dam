<?php

declare(strict_types=1);

namespace App\HttpClient;

use AnzuSystems\CommonBundle\Traits\LoggerAwareRequest;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Model\Dto\Job\JobImageCopyResultDto;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use JsonException;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CmsClient implements LoggerAwareInterface
{
    use LoggerAwareRequest;
    use SerializerAwareTrait;

    private const string COMPLETE_COPY_JOB_PATH = '/api/sys/v1/job-image-copy/%d/complete';

    public function __construct(
        private readonly HttpClientInterface $anzuCmsApiClient,
    ) {
    }

    /**
     * @throws SerializerException
     * @throws JsonException
     */
    public function notifyFinishedJobImageCopy(JobImageCopyResultDto $dto): void
    {
        /** @var array $data */
        $data = $this->serializer->toArray($dto);
        $result = $this->loggedRequest(
            client: $this->anzuCmsApiClient,
            message: '[Anzu CMS] Notify finished job image copy.',
            url: sprintf(self::COMPLETE_COPY_JOB_PATH, (int) $dto->getJobImageCopy()->getId()),
            method: Request::METHOD_PATCH,
            json: $data,
        );

        if ($result->hasError()) {
            throw new RuntimeException('Anzu CMS notify finished job image copy failed');
        }
    }
}
