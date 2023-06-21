<?php

declare(strict_types=1);

namespace App\Controller\Api\Sys\V1;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseCreated;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use App\Domain\Asset\AssetRtmpFacade;
use App\Model\Domain\Asset\AssetFileRtmpDecorator;
use App\Model\Dto\Asset\RtmpAssetDto;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag('Asset')]
#[Route('/asset', 'sys_asset_')]
final class AssetController extends AbstractApiController
{
    public function __construct(
        private readonly AssetRtmpFacade $factory,
    ) {
    }

    /**
     * @throws ValidationException
     * @throws NonUniqueResultException
     * @throws FilesystemException
     */
    #[Route('/rtmp', 'import_from_rtmp', methods: [Request::METHOD_POST])]
    #[OAResponse(AssetLicence::class), OAResponseValidation, OAResponseCreated]
    public function importFromRtmp(#[SerializeParam] RtmpAssetDto $dto): JsonResponse
    {
        return $this->okResponse(
            AssetFileRtmpDecorator::getInstance($this->factory->createFromRtmp($dto))
        );
    }
}
