<?php

declare(strict_types=1);

namespace App\Controller\Api\Ugc\V1;

use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use App\App;
use App\Model\Domain\AssetLicence\AssetLicenceDto;
use App\Security\Voter\UgcVoter;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/asset-licence', name: 'ugc_asset_licence_v1_')]
#[OA\Tag('AssetLicence')]
final class AssetLicenceController extends AbstractApiController
{
    public function __construct(
        private readonly AssetLicenceRepository $assetLicenceRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/blog/{blogId}', name: 'get_by_blog_id', requirements: ['blogId' => '\d+'], methods: [Request::METHOD_GET])]
    #[OAResponse(AssetLicenceDto::class)]
    public function getByBlogId(int $blogId): JsonResponse
    {
        /** @var ExtSystem $extSystem */
        $extSystem = $this->entityManager->getReference(ExtSystem::class, App::BLOG_EXT_SYSTEM_ID);
        $licence = $this->assetLicenceRepository->findOneByExtSystemAndExtId($extSystem, (string) $blogId);

        if (null === $licence) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $licence);

        return $this->okResponse(AssetLicenceDto::getInstance($licence));
    }
}
