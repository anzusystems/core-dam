<?php

declare(strict_types=1);

namespace App\HttpClient;

use AnzuSystems\CommonBundle\Traits\LoggerAwareRequest;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Model\Dto\Job\JobImageCopyResultDto;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Model\Domain\Asset\AssetCmsSysDto;
use App\Model\Domain\Image\CmsImageUsageListDto;
use App\Model\Domain\Image\ImageCmsSysDto;
use Doctrine\Common\Collections\Collection;
use JsonException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CmsClient
{
    use LoggerAwareRequest;
    use SerializerAwareTrait;

    private const string COMPLETE_COPY_JOB_PATH = '/api/sys/v1/dam/job-image-copy/%d/complete';
    private const string IMAGE_USAGAE_PATH = '/api/sys/v1/dam/image/usage';
    private const string DAM_UPDATE_MEDIA = '/api/sys/v1/dam/media';
    private const string DAM_UPDATE_IMAGE = '/api/sys/v1/dam/image';

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
                    static fn (Uuid $id): string => $id->toRfc4122(),
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

    /**
     * @param Collection<int, AssetCmsSysDto> $dtoList
     *
     * @throws JsonException
     * @throws SerializerException
     */
    public function notifyAssetChanged(Collection $dtoList): void
    {
        /** @var array $data */
        $data = $this->serializer->toArray($dtoList);
        $result = $this->loggedRequest(
            client: $this->anzuCmsApiClient,
            message: '[Anzu CMS] Media update',
            url: self::DAM_UPDATE_MEDIA,
            method: Request::METHOD_PATCH,
            json: ['damMediaSysList' => $data],
        );

        if (Response::HTTP_NOT_FOUND === $result->getStatusCode()) {
            return;
        }

        if ($result->hasError()) {
            throw new RuntimeException('Anzu CMS media update failed');
        }
    }

    /**
     * @param Collection<int, ImageCmsSysDto> $dtoList
     *
     * @throws JsonException
     * @throws SerializerException
     */
    public function notifyImageChanged(Collection $dtoList): void
    {
        /** @var array $data */
        $data = $this->serializer->toArray($dtoList);
        $result = $this->loggedRequest(
            client: $this->anzuCmsApiClient,
            message: '[Anzu CMS] Image update',
            url: self::DAM_UPDATE_IMAGE,
            method: Request::METHOD_PATCH,
            json: ['damImageSysList' => $data],
        );

        if (Response::HTTP_NOT_FOUND === $result->getStatusCode()) {
            return;
        }

        if ($result->hasError()) {
            throw new RuntimeException('Anzu CMS image update failed');
        }
    }
}
