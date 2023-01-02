<?php

declare(strict_types=1);

namespace App\Controller\Api\Adm\V1;

use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Domain\Image\ImagePositionFacade;
use AnzuSystems\CoreDamBundle\DataFixtures\ImageFixtures;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFilePositionFacade;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Model\Dto\AssetFileMetadata\AssetSlotAdmListDto;
use AnzuSystems\CoreDamBundle\Model\OpenApi\Request\OARequest;
use AnzuSystems\CoreDamBundle\Repository\AssetRepository;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use AnzuSystems\SerializerBundle\Request\ParamConverter\SerializerParamConverter;
use App\App;
use App\Domain\User\UserFacade;
use App\Entity\User;
use App\Model\Domain\User\CreateUserDto;
use App\Model\Domain\User\UpdateUserDto;
use App\Repository\UserRepository;
use App\Security\Permission\DamPermissions;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/test', 'adm_test')]
final class TestController extends AbstractApiController
{
    public function __construct(
        private readonly ImageFileRepository $imageFileRepository,
        private readonly ImagePositionFacade $assetFilePositionFacade,
    ) {
    }
    
    #[Route('', 'get_current', methods: [Request::METHOD_GET])]
    public function getCurrent(): JsonResponse
    {
        $image11 = $this->imageFileRepository->find(ImageFixtures::IMAGE_ID_1_1);
        $image12 = $this->imageFileRepository->find(ImageFixtures::IMAGE_ID_1_2);
        $image2 = $this->imageFileRepository->find(ImageFixtures::IMAGE_ID_2);
        $image3 = $this->imageFileRepository->find(ImageFixtures::IMAGE_ID_3);

        $asset1 = $image11->getAsset();
        $asset2 = $image2->getAsset();

//        $this->assetFilePositionFacade->setMainFile($asset1, $image3);

//        $this->assetFilePositionFacade->setToSlot($asset1, $image2, 'free');
//        $this->assetFilePositionFacade->setToPosition($asset1, $image12, 'extra');
//        $this->assetFilePositionFacade->setToPosition($asset2, $image11, 'extra');

        return $this->okResponse([
            $asset1->getSlots()->map(
                fn (AssetSlot $assetSlot): AssetSlotAdmListDto => AssetSlotAdmListDto::getInstance($assetSlot)
            )->toArray(),
//            $asset2->getSlots()->map(
//                fn (AssetSlot $assetSlot): AssetSlotAdmListDto => AssetSlotAdmListDto::getInstance($assetSlot)
//            )->toArray()
        ]);
    }
}
