<?php

declare(strict_types=1);

namespace App\Request\ValueResolver;

use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use App\Model\Attributes\AssetLicenceByBlogIdParam;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AutoconfigureTag(name: 'controller.argument_value_resolver', attributes: ['priority' => 200])]
final readonly class AssetLicenceByBlogIdValueResolver implements ValueResolverInterface
{
    private const BLOG_EXT_SYSTEM_ID = 4;

    public function __construct(
        private AssetLicenceRepository $assetLicenceRepo,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ORMException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        /** @var AssetLicenceByBlogIdParam|null $attribute */
        $attribute = $argument->getAttributesOfType(AssetLicenceByBlogIdParam::class)[0] ?? null;
        if (false === ($attribute instanceof AssetLicenceByBlogIdParam)) {
            return [];
        }
        $blogId = $request->attributes->getInt($attribute->name);
        if (0 === $blogId) {
            return [];
        }

        /** @var ExtSystem $extSystem */
        $extSystem = $this->entityManager->getReference(ExtSystem::class, self::BLOG_EXT_SYSTEM_ID);
        $licence = $this->assetLicenceRepo->findOneByExtSystemAndExtId($extSystem, (string) $blogId);
        if (null === $licence) {
            throw new NotFoundHttpException();
        }

        return [$licence];
    }
}
