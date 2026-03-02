<?php

declare(strict_types=1);

namespace App\Controller\Api\Ugc\V1;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Request\OARequest;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Domain\RegionOfInterest\RegionOfInterestFacade;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\RegionOfInterest;
use AnzuSystems\CoreDamBundle\Model\Dto\RegionOfInterest\RegionOfInterestAdmDetailDto;
use AnzuSystems\CoreDamBundle\Model\Dto\RegionOfInterest\RegionOfInterestAdmListDto;
use AnzuSystems\CoreDamBundle\Repository\Decorator\RegionOfInterestRepositoryDecorator;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use App\Security\Voter\UgcVoter;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '', name: 'ugc_roi_v1_')]
#[OA\Tag('RegionOfInterest')]
final class RegionOfInterestController extends AbstractApiController
{
    public function __construct(
        private readonly RegionOfInterestFacade $regionOfInterestFacade,
        private readonly RegionOfInterestRepositoryDecorator $repositoryDecorator,
    ) {
    }

    /**
     * Get list of ROIs for an image.
     *
     * @throws ORMException
     */
    #[Route('/image/{image}/roi', name: 'get_list', methods: [Request::METHOD_GET])]
    #[OAParameterPath('image'), OAResponse([RegionOfInterestAdmListDto::class])]
    public function getList(ImageFile $image, ApiParams $apiParams): JsonResponse
    {
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $image);

        return $this->okResponse(
            $this->repositoryDecorator->findByApiParamsWithInfiniteListing($apiParams, $image),
        );
    }

    /**
     * Get one ROI.
     */
    #[Route(path: '/roi/{regionOfInterest}', name: 'get_one', methods: [Request::METHOD_GET])]
    #[OAParameterPath('regionOfInterest'), OAResponse(RegionOfInterestAdmDetailDto::class)]
    public function getOne(RegionOfInterest $regionOfInterest): JsonResponse
    {
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $regionOfInterest);

        return $this->okResponse(RegionOfInterestAdmDetailDto::getInstance($regionOfInterest));
    }

    /**
     * Update one ROI.
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route(path: '/roi/{regionOfInterest}', name: 'update', methods: [Request::METHOD_PUT])]
    #[
        OAParameterPath('regionOfInterest'),
        OARequest(RegionOfInterestAdmDetailDto::class),
        OAResponse(RegionOfInterestAdmDetailDto::class),
        OAResponseValidation
    ]
    public function update(RegionOfInterest $regionOfInterest, #[SerializeParam] RegionOfInterestAdmDetailDto $roiDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $regionOfInterest);

        return $this->okResponse(
            RegionOfInterestAdmDetailDto::getInstance(
                $this->regionOfInterestFacade->update($regionOfInterest, $roiDto)
            )
        );
    }
}
