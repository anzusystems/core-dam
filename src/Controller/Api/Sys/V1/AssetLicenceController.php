<?php

declare(strict_types=1);

namespace App\Controller\Api\Sys\V1;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseCreated;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use AnzuSystems\CoreDamBundle\Repository\ExtSystemRepository;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use App\Domain\AssetLicence\AssetLicenceFacade;
use App\Model\Domain\AssetLicence\UpsertAssertLicenceDto;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag('AssetLicence')]
final class AssetLicenceController extends AbstractApiController
{
    public function __construct(
        private readonly AssetLicenceFacade $assetLicenceFacade,
        private readonly AssetLicenceRepository $licenceRepository,
        private readonly ExtSystemRepository $extSystemRepository,
    ) {
    }

    /**
     * @throws ValidationException
     */
    #[Route('licence-group', 'upsert_legacy', methods: [Request::METHOD_PUT])]
    #[Route('asset-licence', 'upsert', methods: [Request::METHOD_PUT])]
    #[OAResponse(AssetLicence::class), OAResponseValidation, OAResponseCreated]
    public function upsertLicence(#[SerializeParam] UpsertAssertLicenceDto $upsertAssertLicenceDto): JsonResponse
    {
        /** @var ExtSystem $extSystem */
        $extSystem = $this->extSystemRepository->findOneBySlug($upsertAssertLicenceDto->getExtSystemSlug());
        $upsertAssertLicenceDto->setExtSystem($extSystem);
        $existingLicence = $this->licenceRepository->findOneByExtSystemAndExtId(
            extSystem: $extSystem,
            extId: $upsertAssertLicenceDto->getExtId(),
        );

        if ($existingLicence instanceof AssetLicence) {
            return $this->okResponse(
                $this->assetLicenceFacade->update($existingLicence, $upsertAssertLicenceDto),
            );
        }

        return $this->createdResponse(
            $this->assetLicenceFacade->create($upsertAssertLicenceDto),
        );
    }
}
