<?php

declare(strict_types=1);

namespace App\Controller\Api\Ugc\V1;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Log\Helper\AuditLogResourceHelper;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Domain\Chunk\ChunkFacade;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFacade;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageStatusFacade;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\Chunk;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Dto\Asset\AssetAdmFinishDto;
use AnzuSystems\CoreDamBundle\Model\Dto\Chunk\ChunkAdmCreateDto;
use AnzuSystems\CoreDamBundle\Model\Dto\Image\ImageAdmCreateDto;
use AnzuSystems\CoreDamBundle\Model\Dto\Image\ImageFileAdmDetailDto;
use AnzuSystems\CoreDamBundle\Model\OpenApi\Request\OARequest;
use App\Security\Voter\UgcVoter;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/image', name: 'ugc_image_v1_')]
#[OA\Tag('Image')]
final class ImageController extends AbstractApiController
{
    public function __construct(
        private readonly ImageFacade $imageFacade,
        private readonly ImageStatusFacade $statusFacade,
        private readonly ChunkFacade $chunkFacade,
    ) {
    }

    /**
     * Get one image.
     */
    #[Route(path: '/{image}', name: 'get_one', methods: [Request::METHOD_GET])]
    #[OAParameterPath('image'), OAResponse(ImageFileAdmDetailDto::class)]
    public function getOne(ImageFile $image): JsonResponse
    {
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $image);

        return $this->okResponse(ImageFileAdmDetailDto::getInstance($image));
    }

    /**
     * Create an image with specific licence
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route(path: '/licence/{assetLicence}', name: 'create', methods: [Request::METHOD_POST])]
    #[OAParameterPath('assetLicence'), OARequest(ImageAdmCreateDto::class), OAResponse(ImageFileAdmDetailDto::class), OAResponseValidation]
    public function create(Request $request, #[SerializeParam] ImageAdmCreateDto $image, AssetLicence $assetLicence): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $assetLicence);
        $image = $this->imageFacade->createAssetFile($image, $assetLicence);
        AuditLogResourceHelper::setResourceByEntity(request: $request, entity: $image);

        return $this->createdResponse(ImageFileAdmDetailDto::getInstance($image));
    }

    /**
     * Add chunk to ImageFile
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route(path: '/{image}/chunk', name: 'add_chunk', methods: [Request::METHOD_POST])]
    #[OAParameterPath('image'), OARequest(ChunkAdmCreateDto::class), OAResponse(Chunk::class), OAResponseValidation]
    public function addChunk(Request $request, ImageFile $image, ChunkAdmCreateDto $chunk): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $image);
        AuditLogResourceHelper::setResourceByEntity(request: $request, entity: $image);

        return $this->createdResponse(
            $this->chunkFacade->create($chunk, $image)
        );
    }

    /**
     * Finish upload and start postprocess
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     * @throws SerializerException
     */
    #[Route(path: '/{image}/uploaded', name: 'finish_upload', methods: [Request::METHOD_PATCH])]
    #[OAParameterPath('image'), OARequest(AssetAdmFinishDto::class), OAResponse(ImageFileAdmDetailDto::class), OAResponseValidation]
    public function finishUpload(Request $request, #[SerializeParam] AssetAdmFinishDto $assetFinishDto, ImageFile $image): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $image);
        AuditLogResourceHelper::setResourceByEntity(request: $request, entity: $image);

        return $this->okResponse(
            ImageFileAdmDetailDto::getInstance(
                $this->statusFacade->finishUpload($assetFinishDto, $image)
            )
        );
    }

    /**
     * @throws AppReadOnlyModeException
     */
    #[Route(path: '/{image}/rotate/{angle}', name: 'rotate', requirements: ['angle' => '(90)|(180)|(270)'], methods: [Request::METHOD_PATCH])]
    #[OAParameterPath('image'), OAResponse(ImageFileAdmDetailDto::class), OAResponseValidation]
    public function rotate(Request $request, ImageFile $image, float $angle): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $image);
        AuditLogResourceHelper::setResourceByEntity(request: $request, entity: $image);

        return $this->okResponse(
            ImageFileAdmDetailDto::getInstance(
                $this->imageFacade->rotateImage($image, $angle)
            )
        );
    }
}
