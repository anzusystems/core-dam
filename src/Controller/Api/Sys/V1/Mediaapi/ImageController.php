<?php

declare(strict_types=1);

namespace App\Controller\Api\Sys\V1\Mediaapi;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseCreated;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetFacade;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use App\Domain\Image\MediaApi\ImageFacade;
use App\Domain\Image\MediaApi\ImageFactory;
use App\Model\Domain\Asset\AssetFileMediaApiCreateDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiResponseDecorator;
use App\Model\Domain\Asset\AssetFileRtmpDecorator;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag('Mediaapi Image')]
#[Route('/mediaapi/image', 'sys_mediaapi_image')]
final class ImageController extends AbstractApiController
{
    public function __construct(
        private readonly ImageFacade $imageFacade,
        private readonly AssetFacade $assetFacade,
    ) {
    }

    /**
     * @throws AppReadOnlyModeException
     * @throws FilesystemException
     * @throws NonUniqueResultException
     * @throws ValidationException
     */
    #[Route('', 'create', methods: [Request::METHOD_POST])]
    #[OAResponse(AssetFileMediaApiResponseDecorator::class), OAResponseValidation, OAResponseCreated]
    public function create(#[SerializeParam] AssetFileMediaApiCreateDecorator $dto): JsonResponse
    {
        App::throwOnReadOnlyMode();

        return $this->createdResponse(
            AssetFileMediaApiResponseDecorator::getInstance($this->imageFacade->create($dto))
        );
    }

    /**
     * @throws AppReadOnlyModeException
     * @throws ValidationException
     */
    #[Route('/{image}', 'update', methods: [Request::METHOD_PUT])]
    #[OAParameterPath(AssetFileMediaApiResponseDecorator::class), OAResponseValidation, OAResponseCreated]
    public function update(ImageFile $image, #[SerializeParam] AssetFileMediaApiDecorator $dto): JsonResponse
    {
        App::throwOnReadOnlyMode();

        return $this->okResponse(
            AssetFileMediaApiResponseDecorator::getInstance($this->imageFacade->update($image, $dto))
        );
    }

    /**
     * @throws AppReadOnlyModeException
     */
    #[Route('/{image}', 'delete', methods: [Request::METHOD_DELETE])]
    #[OAParameterPath(ImageFile::class), OAResponseValidation, OAResponseCreated]
    public function delete(ImageFile $image): JsonResponse
    {
        App::throwOnReadOnlyMode();

        $this->assetFacade->toDeleting($image->getAsset());

        return $this->noContentResponse();
    }
}
