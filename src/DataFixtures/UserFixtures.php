<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\CoreDamBundle\DataFixtures\AssetLicenceFixtures;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use App\Domain\User\UserManager;
use App\Entity\User;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends AbstractFixtures<User>
 */
final class UserFixtures extends AbstractFixtures
{
    private const CMS_EXT_SYSTEM_ID = 1;

    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly PermissionGroupFixtures $permissionGroupFixtures,
        private readonly AssetLicenceFixtures $assetLicenceFixtures,
    ) {
    }

    public static function getIndexKey(): string
    {
        return User::class;
    }

    public static function getDependencies(): array
    {
        return [PermissionGroupFixtures::class, AssetLicenceFixtures::class];
    }

    public function useCustomId(): bool
    {
        return true;
    }

    public function load(ProgressBar $progressBar): void
    {
        foreach ($progressBar->iterate($this->getData()) as $user) {
            $user = $this->userManager->create($user);
            $this->addToRegistry($user, (int) $user->getId());
        }
    }

    /**
     * @return iterable<User>
     *
     * @throws ORMException
     */
    private function getData(): iterable
    {
        $adminUser = (new User())
            ->setId(User::ID_ADMIN)
            ->setEmail('dam_admin@anzusystems.dev')
            ->setRoles([AnzuUser::ROLE_ADMIN])
            ->setEnabled(true)
        ;
        $password = $this->userPasswordHasher->hashPassword($adminUser, 'admin');
        $adminUser
            ->setPassword($password)
        ;

        yield $adminUser;

        $cmsExtSystem = $this->userManager->getEntityManager()->getReference(ExtSystem::class, self::CMS_EXT_SYSTEM_ID);
        $basicUser = (new User())
            ->setId(User::ID_BASIC_USER)
            ->setEmail('dam_basic@anzusystems.dev')
            ->setRoles([AnzuUser::ROLE_USER])
            ->setEnabled(true)
        ;
        $password = $this->userPasswordHasher->hashPassword($basicUser, 'basic');
        $basicUser
            ->setPassword($password)
        ;
        $basicUser
            ->getPermissionGroups()
            ->add(
                $this->permissionGroupFixtures->getOneFromRegistry(PermissionGroupFixtures::BASIC_GROUP_TITLE)
            )
        ;
        $basicUser
            ->getAssetLicences()
            ->add(
                $this->assetLicenceFixtures->getOneFromRegistry(AssetLicenceFixtures::DEFAULT_LICENCE_ID)
            )
        ;
        $basicUser
            ->getUserToExtSystems()
            ->add($cmsExtSystem);

        yield $basicUser;
    }
}
