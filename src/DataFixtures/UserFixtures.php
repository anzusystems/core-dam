<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\Contracts\Model\User\UserDto;
use AnzuSystems\CoreDamBundle\DataFixtures\AssetLicenceFixtures as BaseAssetLicenceFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\PermissionGroupFixtures;
use App\Domain\User\UserManager;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractFixtures<User>
 */
final class UserFixtures extends AbstractFixtures
{
    public const USER_ONE_SSO_ID = 10_001_040;
    public const USER_TWO_SSO_ID = 10_001_043;

    public function __construct(
        private readonly UserManager $userManager,
        private readonly AssetLicenceFixtures $assetLicenceFixtures,
        private readonly BaseAssetLicenceFixtures $baseAssetLicenceFixtures,
        private readonly PermissionGroupFixtures $permissionGroupFixtures,
    ) {
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
            /** @var User $created */
            $created = $this->userManager->createAnzuUser($user, $userDto);
            $this->addToRegistry($created, (int) $created->getId());
            $this->addToRegistry($created, $created->getId());
        }
        $this->userManager->flush();
    }

    /**
     * @return iterable<UserDto, User>
     */
    private function getData(): iterable
    {
        $permissionGroup = $this->permissionGroupFixtures->getOneFromRegistry(PermissionGroupFixtures::BASIC_GROUP_TITLE);
        $defaultCmsLicence = $this->baseAssetLicenceFixtures->getOneFromRegistry(
            key: BaseAssetLicenceFixtures::DEFAULT_LICENCE_ID
        );
        $defaultBlogLicence = $this->assetLicenceFixtures->getOneFromRegistry(
            key: AssetLicenceFixtures::BLOG_DEFAULT_ASSET_LICENCE_ID
        );

        yield (new UserDto())
            ->setId(self::USER_ONE_SSO_ID)
            ->setEmail('user1.anzu@smeonline.sk')
            ->setPermissionGroups(new ArrayCollection([$permissionGroup]))
        => (new User())
            ->setAssetLicences(new ArrayCollection([$defaultCmsLicence]))
        ;

        yield (new UserDto())
            ->setId(self::USER_TWO_SSO_ID)
            ->setEmail('user2.anzu@smeonline.sk')
            ->setPermissionGroups(new ArrayCollection([$permissionGroup]))
        => (new User())
            ->setRoles([User::ROLE_UGC, User::ROLE_USER])
            ->setAssetLicences(new ArrayCollection([$defaultCmsLicence, $defaultBlogLicence]))
        ;

        yield (new UserDto())
            ->setId(1_799_719)
            ->setEmail('lubomir.stanko@petitpress.sk')
            ->setRoles([User::ROLE_ADMIN])
        => new User();

        yield (new UserDto())
            ->setId(1_791_571)
            ->setEmail('lukas.budos@petitpress.sk')
            ->setRoles([User::ROLE_ADMIN])
        => new User();

        yield (new UserDto())
            ->setId(1_459_820)
            ->setEmail('ronald.marfoldi@petitpress.sk')
            ->setRoles([User::ROLE_ADMIN])
        => new User();

        yield (new UserDto())
            ->setId(1_803_193)
            ->setEmail('david.kapsdorfer@petitpress.sk')
            ->setRoles([User::ROLE_ADMIN])
        => new User();

        yield (new UserDto())
            ->setId(1_777_852)
            ->setEmail('igor.petriska@petitpress.sk')
            ->setRoles([User::ROLE_ADMIN])
        => new User();

        yield (new UserDto())
            ->setId(1_651_144)
            ->setEmail('stanislav.volar@petitpress.sk')
            ->setRoles([User::ROLE_ADMIN])
        => new User();

        yield (new UserDto())
            ->setId(1_476_581)
            ->setEmail('tomas.hermanek@petitpress.sk')
            ->setRoles([User::ROLE_ADMIN])
        => new User();

        yield (new UserDto())
            ->setId(1_971_254)
            ->setEmail('matej.mihalik@petitpress.sk')
            ->setRoles([User::ROLE_ADMIN])
        => new User();
    }
}
