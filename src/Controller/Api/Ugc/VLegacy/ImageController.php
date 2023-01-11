<?php

declare(strict_types=1);

namespace App\Controller\Api\Ugc\VLegacy;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFacade;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\ApiFilter\ApiUgcLegacyParams;
use App\Model\AssetLicenceDecorator;
use App\Model\Attribute\AssetLicenceByBlogIdParam;
use App\Model\Ugc\Legacy\ImageDetailDto;
use App\Model\Ugc\Legacy\ImageListDto;
use App\Repository\Decorator\ImageUgcLegacyRepositoryDecorator;
use App\Security\Voter\UgcVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('', 'ugc_image_vlegacy_')]
final class ImageController extends AbstractApiController
{
    public function __construct(
        private readonly ImageUgcLegacyRepositoryDecorator $ugcLegacyRepositoryDecorator,
        private readonly ImageFacade $imageFacade,
    ) {
    }

    /**
     * @throws SerializerException
     * @throws ValidationException
     */
    #[Route('/blog/{blogId}/image', name: 'list', methods: [Request::METHOD_GET])]
    #[Route('/blog/{blogId}/image/search', name: 'search', methods: [Request::METHOD_GET])]
    #[ParamConverter('blogId', isOptional: true)]
    #[OAResponse([ImageListDto::class])]
    public function searchList(
        #[AssetLicenceByBlogIdParam(name: 'blogId')] AssetLicenceDecorator $licence,
        ApiUgcLegacyParams $apiUgcLegacyParams,
    ): JsonResponse {
        $licence = $licence->getAssetLicence();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $licence);

        return $this->okResponse(
            $this->ugcLegacyRepositoryDecorator->searchList($licence, $apiUgcLegacyParams),
        );
    }

    #[Route(path: '/image/{imageFile}', name: 'get_one', methods: [Request::METHOD_GET])]
    #[OAResponse(ImageDetailDto::class)]
    public function getOne(ImageFile $imageFile): JsonResponse
    {
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $imageFile);

        return $this->okResponse(ImageDetailDto::getInstance($imageFile));
    }

    /**
     * @throws AppReadOnlyModeException
     */
    #[Route(path: '/image/{imageFile}/rotate/{angle}', name: 'rotate', requirements: ['angle' => '(90)|(180)|(270)'], methods: [Request::METHOD_PATCH])]
    #[OAParameterPath('imageFile'), OAResponse(ImageDetailDto::class), OAResponseValidation]
    public function rotate(ImageFile $imageFile, float $angle): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $imageFile);

        return $this->okResponse(
            ImageDetailDto::getInstance(
                $this->imageFacade->rotateImage($imageFile, $angle)
            )
        );
    }

    /**
     * @throws AppReadOnlyModeException
     */
    #[Route(path: '/image/{imageFile}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(ImageFile $imageFile): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $imageFile);
        $this->imageFacade->delete($imageFile);

        return $this->noContentResponse();
    }
}
