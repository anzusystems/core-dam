<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\CommonBundle\Model\User\UserDto;
use AnzuSystems\Contracts\Entity\Embeds\Person;
use AnzuSystems\CoreDamBundle\DataFixtures\AssetLicenceFixtures as BaseAssetLicenceFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PermissionGroupFixtures;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\AssetLicenceGroup;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceGroupRepository;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use App\App;
use App\Domain\User\DeprecatedUserManager;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractFixtures<User>
 */
final class UserFixtures extends AbstractFixtures
{
    public const int USER_ONE_SSO_ID = 10_001_040;
    public const int USER_TWO_SSO_ID = 10_001_043;
    public const int USER_THREE_SSO_ID = 10_001_045;
    public const int USER_FOUR_SSO_ID = 111_000_000;
    public const int USER_FIVE_SSO_ID = 111_000_001;

    public function __construct(
        private readonly DeprecatedUserManager $userManager,
        private readonly AssetLicenceFixtures $assetLicenceFixtures,
        private readonly PermissionGroupFixtures $permissionGroupFixtures,
        private readonly AssetLicenceRepository $assetLicenceRepository,
        private readonly AssetLicenceGroupRepository $assetLicenceGroupRepository,
    ) {
    }

    public function getEnvironments(): array
    {
        return ['dev', 'test'];
    }

    public static function getIndexKey(): string
    {
        return User::class;
    }

    public static function getDependencies(): array
    {
        return [AssetLicenceFixtures::class, PermissionGroupFixtures::class];
    }

    public function useCustomId(): bool
    {
        return true;
    }

    public function load(ProgressBar $progressBar): void
    {
        /** @var UserDto $userDto */
        foreach ($progressBar->iterate($this->getData()) as $userDto => $user) {
            /** @var User|null $existingUser */
            if ($user->getId()) {
                /** @var User $updated */
                $updated = $this->userManager->updateAnzuUser($user, $userDto);
                $this->addToRegistry($updated, (int) $updated->getId());

                continue;
            }

            /** @var User $created */
            $created = $this->userManager->createAnzuUser($user, $userDto);
            $this->addToRegistry($created, (int) $created->getId());
        }
        $this->userManager->flush();
    }

    /**
     * @return iterable<UserDto, User>
     */
    private function getData(): iterable
    {
        $permissionGroup = $this->permissionGroupFixtures->getOneFromRegistry(PermissionGroupFixtures::BASIC_GROUP_TITLE);
        /** @var AssetLicence $defaultCmsLicence */
        $defaultCmsLicence = $this->assetLicenceRepository->find(BaseAssetLicenceFixtures::DEFAULT_LICENCE_ID);
        /** @var AssetLicenceGroup $cmsLicenceGroup */
        $cmsLicenceGroup = $this->assetLicenceGroupRepository->find(1);

        /** @var AssetLicence $blogOneLicence */
        $blogOneLicence = $this->assetLicenceFixtures->getOneFromRegistry(
            key: AssetLicenceFixtures::BLOG_ONE_LICENCE_ID
        );
        /** @var AssetLicence $blogTwoLicence */
        $blogTwoLicence = $this->assetLicenceFixtures->getOneFromRegistry(
            key: AssetLicenceFixtures::BLOG_TWO_LICENCE_ID
        );
        /** @var AssetLicence $blogThreeLicence */
        $blogThreeLicence = $this->assetLicenceFixtures->getOneFromRegistry(
            key: AssetLicenceFixtures::BLOG_THREE_LICENCE_ID
        );
        /** @var AssetLicence $blogFourLicence */
        $blogFourLicence = $this->assetLicenceFixtures->getOneFromRegistry(
            key: AssetLicenceFixtures::BLOG_FOUR_LICENCE_ID
        );
        /** @var AssetLicence $blogFiveLicence */
        $blogFiveLicence = $this->assetLicenceFixtures->getOneFromRegistry(
            key: AssetLicenceFixtures::BLOG_FIVE_LICENCE_ID
        );
        /** @var AssetLicence $blogSixLicence */
        $blogSixLicence = $this->assetLicenceFixtures->getOneFromRegistry(
            key: AssetLicenceFixtures::BLOG_SIX_LICENCE_ID
        );

        /** @psalm-suppress InvalidArgument */
        yield (new UserDto())
            ->setId(self::USER_ONE_SSO_ID)
            ->setEmail('user1.anzu@smeonline.sk')
            ->setPermissionGroups(new ArrayCollection([$permissionGroup]))
            ->setRoles([User::ROLE_UGC, User::ROLE_SUPER_ADMIN])
            ->setPerson(
                (new Person())
                    ->setFullName('User1 Anzu')
            )
        => (new User())
            ->setSelectedLicence($defaultCmsLicence)
            ->setAssetLicences(new ArrayCollection([$defaultCmsLicence, $blogOneLicence]))
            ->setUserToExtSystems(new ArrayCollection([$defaultCmsLicence->getExtSystem(), $blogOneLicence->getExtSystem()]))
        ;

        /** @psalm-suppress InvalidArgument */
        yield (new UserDto())
            ->setId(self::USER_TWO_SSO_ID)
            ->setEmail('user2.anzu@smeonline.sk')
            ->setPermissionGroups(new ArrayCollection([$permissionGroup]))
            ->setRoles([User::ROLE_UGC, User::ROLE_SUPER_ADMIN])
            ->setPerson(
                (new Person())
                    ->setFullName('User2 Anzu')
            )
        => (new User())
            ->setLicenceGroups(new ArrayCollection([$cmsLicenceGroup]))
            ->setSelectedLicence($defaultCmsLicence)
            ->setAssetLicences(new ArrayCollection([$blogTwoLicence]))
            ->setUserToExtSystems(new ArrayCollection([$defaultCmsLicence->getExtSystem(), $blogTwoLicence->getExtSystem()]))
        ;

        /** @psalm-suppress InvalidArgument */
        yield (new UserDto())
            ->setId(self::USER_THREE_SSO_ID)
            ->setEmail('user3.anzu@smeonline.sk')
            ->setRoles([User::ROLE_UGC])
            ->setPerson(
                (new Person())
                    ->setFullName('User3 Anzu')
            )
        => (new User())
            ->setSelectedLicence($blogThreeLicence)
            ->setAssetLicences(new ArrayCollection([$blogThreeLicence]))
            ->setUserToExtSystems(new ArrayCollection([$blogThreeLicence->getExtSystem()]))
        ;

        /** @var User|null $userAdmin */
        $userAdmin = $this->entityManager->find(User::class, App::getUserIdAdmin());
        $userAdmin ??= new User();

        /** @psalm-suppress InvalidArgument */
        yield (new UserDto())
            ->setId(App::getUserIdAdmin())
            ->setEmail('admin.anzu@smeonline.sk')
            ->setRoles([User::ROLE_UGC, User::ROLE_SUPER_ADMIN])
            ->setPerson(
                (new Person())
                    ->setFullName('Admin Anzu')
            )
        => $userAdmin
            ->setSelectedLicence($defaultCmsLicence)
            ->setAssetLicences(new ArrayCollection([$blogFourLicence]))
            ->setUserToExtSystems(new ArrayCollection([$blogFourLicence->getExtSystem()]))
        ;

        /** @psalm-suppress InvalidArgument */
        yield (new UserDto())
            ->setId(self::USER_FOUR_SSO_ID)
            ->setEmail('user1_test.anzu@smeonline.sk')
            ->setRoles([User::ROLE_UGC])
            ->setPerson(
                (new Person())
                    ->setFullName('User1 Test')
            )
        => (new User())
            ->setSelectedLicence($blogFiveLicence)
            ->setAssetLicences(new ArrayCollection([$blogFiveLicence]))
            ->setUserToExtSystems(new ArrayCollection([$blogFiveLicence->getExtSystem()]))
        ;

        /** @psalm-suppress InvalidArgument */
        yield (new UserDto())
            ->setId(self::USER_FIVE_SSO_ID)
            ->setEmail('user2_test.anzu@smeonline.sk')
            ->setRoles([User::ROLE_UGC])
            ->setPerson(
                (new Person())
                    ->setFullName('User2 Test')
            )
        => (new User())
            ->setSelectedLicence($blogSixLicence)
            ->setAssetLicences(new ArrayCollection([$blogSixLicence]))
            ->setUserToExtSystems(new ArrayCollection([$blogSixLicence->getExtSystem()]))
        ;
    }
}
