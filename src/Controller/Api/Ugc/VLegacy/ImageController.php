<?php

declare(strict_types=1);

namespace App\Controller\Api\Ugc\VLegacy;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Request\OARequest;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\Contracts\Exception\AnzuException;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageStatusFacade;
use AnzuSystems\CoreDamBundle\Entity\Chunk;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Dto\Asset\AssetAdmFinishDto;
use AnzuSystems\CoreDamBundle\Model\Dto\Chunk\ChunkAdmCreateDto;
use AnzuSystems\CoreDamBundle\Model\Dto\Image\ImageFileAdmDetailDto;
use AnzuSystems\CoreDamBundle\Request\ParamConverter\ChunkParamConverter;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use AnzuSystems\SerializerBundle\Request\ParamConverter\SerializerParamConverter;
use App\ApiFilter\ApiUgcLegacyParams;
use App\Domain\AssetFile\ImageUgcLegacyFacade;
use App\Domain\Chunk\ChunkUgcLegacyFacade;
use App\Exception\DuplicateImageFileException;
use App\Model\AssetLicenceDecorator;
use App\Model\Attribute\AssetLicenceByBlogIdParam;
use App\Model\Attribute\SerializeIterableParam;
use App\Model\Ugc\Legacy\ChunkCreatedDto;
use App\Model\Ugc\Legacy\ImageCreateDto;
use App\Model\Ugc\Legacy\ImageDetailDto;
use App\Model\Ugc\Legacy\ImageListDto;
use App\Model\Ugc\Legacy\ImageUpdateDto;
use App\Repository\Decorator\ImageUgcLegacyRepositoryDecorator;
use App\Security\Voter\UgcVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('', 'ugc_image_vlegacy_')]
final class ImageController extends AbstractApiController
{
    public function __construct(
        private readonly ImageUgcLegacyRepositoryDecorator $ugcLegacyRepositoryDecorator,
        private readonly ImageUgcLegacyFacade $imageFacade,
        private readonly ChunkUgcLegacyFacade $chunkFacade,
        private readonly ImageStatusFacade $statusFacade,
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
        #[AssetLicenceByBlogIdParam(name: 'blogId')] AssetLicenceDecorator $licenceDecorator,
        ApiUgcLegacyParams $apiUgcLegacyParams,
    ): JsonResponse {
        $licence = $licenceDecorator->getLicence();
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
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     * @throws NonUniqueResultException
     */
    #[Route('/blog/{blogId}/image', name: 'create', methods: [Request::METHOD_POST])]
    #[ParamConverter('imageCreateDto', converter: SerializerParamConverter::class)]
    #[OAResponse(ImageDetailDto::class)]
    public function create(
        ImageCreateDto $imageCreateDto,
        #[AssetLicenceByBlogIdParam(name: 'blogId')] AssetLicenceDecorator $licenceDecorator,
    ): JsonResponse {
        App::throwOnReadOnlyMode();
        $licence = $licenceDecorator->getLicence();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $licence);

        try {
            return $this->createdResponse(
                ImageDetailDto::getInstance(
                    $this->imageFacade->create($imageCreateDto, $licence)
                )
            );
        } catch (DuplicateImageFileException $exception) {
            return $this->okResponse(
                ImageDetailDto::getInstance($exception->getImageFile()),
            );
        }
    }

    /**
     * Add chunk to ImageFile
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route(path: '/image/{imageFile}/chunk', name: 'add_chunk', methods: [Request::METHOD_POST])]
    #[ParamConverter('chunk', converter: ChunkParamConverter::class)]
    #[OAParameterPath('image'), OARequest(ChunkAdmCreateDto::class), OAResponse(Chunk::class), OAResponseValidation]
    public function addChunk(ImageFile $imageFile, ChunkAdmCreateDto $chunk): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $imageFile);

        return $this->createdResponse(
            ChunkCreatedDto::getInstance(
                $this->chunkFacade->create($chunk, $imageFile)
            )
        );
    }

    /**
     * @throws AnzuException
     */
    #[ParamConverter('imageUpdateDto', converter: SerializerParamConverter::class)]
    #[Route(path: '/image/{imageFile}', name: 'update', methods: [Request::METHOD_PUT])]
    public function update(ImageFile $imageFile, ImageUpdateDto $imageUpdateDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $imageFile);
        $imageUpdateDto->setId((string) $imageFile->getId());

        return $this->okResponse(
            ImageDetailDto::getInstance(
                $this->imageFacade->update($imageUpdateDto, false),
            )
        );
    }

    /**
     * @throws AnzuException
     */
    #[Route(path: '/image/bulk-update', name: 'update_bulk', methods: [Request::METHOD_PATCH])]
    #[Route(path: '/image/bulk-update-undescribed', name: 'update_bulk_undescribed', defaults: ['onlyUndescribed' => true], methods: [Request::METHOD_PATCH])]
    public function updateBulk(
        #[SerializeIterableParam(type: ImageUpdateDto::class, maxItems: 10)] ArrayCollection $newImageFiles,
        bool $onlyUndescribed = false,
    ): JsonResponse {
        App::throwOnReadOnlyMode();

        return $this->okResponse(
            $this->imageFacade
                ->updateBulk($newImageFiles, $onlyUndescribed)
                ->map(static fn (ImageFile $imageFile): ImageDetailDto => ImageDetailDto::getInstance($imageFile)),
        );
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
     * Finish upload and start postprocess
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route(path: '/image/{imageFile}/uploaded', name: 'finish_upload', methods: [Request::METHOD_PATCH])]
    #[ParamConverter('assetFinishDto', converter: SerializerParamConverter::class)]
    #[OAParameterPath('imageFile'), OARequest(AssetAdmFinishDto::class), OAResponse(ImageFileAdmDetailDto::class), OAResponseValidation]
    public function finishUpload(AssetAdmFinishDto $assetFinishDto, ImageFile $imageFile): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $imageFile);

        return $this->okResponse(
            ImageDetailDto::getInstance(
                $this->statusFacade->finishUpload($assetFinishDto, $imageFile)
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
