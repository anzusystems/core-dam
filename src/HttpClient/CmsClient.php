<?php

declare(strict_types=1);

namespace App\HttpClient;

use AnzuSystems\CommonBundle\Traits\LoggerAwareRequest;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Model\Dto\Job\JobImageCopyResultDto;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Model\Domain\Image\CmsImageUsageListDto;
use JsonException;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CmsClient implements LoggerAwareInterface
{
    use LoggerAwareRequest;
    use SerializerAwareTrait;

    private const string COMPLETE_COPY_JOB_PATH = '/api/sys/v1/dam/job-image-copy/%d/complete';
    private const string IMAGE_USAGAE_PATH = '/api/sys/v1/dam/image/usage';

    public function __construct(
        private readonly HttpClientInterface $anzuCmsApiClient,
    ) {
    }

    /**
     * @param array<array-key, Uuid> $damIds
     */
    public function getImageUsage(array $damIds): CmsImageUsageListDto
    {
        $result = $this->loggedRequest(
            client: $this->anzuCmsApiClient,
            message: '[Anzu CMS] get image usage',
            url: self::IMAGE_USAGAE_PATH . '?' . http_build_query([
                'damIds' => implode(',', array_map(
                    fn (Uuid $id): string => $id->toRfc4122(),
                    $damIds
                )),
            ]),
        );

        if ($result->hasError()) {
            throw new RuntimeException('Failed to get image usage from Anzu CMS.');
        }

        return $this->serializer->deserialize($result->getContent(), CmsImageUsageListDto::class);
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
