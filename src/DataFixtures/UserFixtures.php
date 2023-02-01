<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\Contracts\Model\User\UserDto;
use AnzuSystems\CoreDamBundle\DataFixtures\AssetLicenceFixtures as BaseAssetLicenceFixtures;
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
        foreach ($progressBar->iterate($this->getData()) as $userDto) {
            $user = $this->userManager->createAnzuUser(new User(), $userDto);
            $this->afterPersistAction($user);
            $this->addToRegistry($user, $user->getId());
        }
        $this->userManager->flush();
    }

    /**
     * @return iterable<int, UserDto>
     */
    private function getData(): iterable
    {
        $permissionGroup = $this->permissionGroupFixtures->getOneFromRegistry(PermissionGroupFixtures::BASIC_GROUP_TITLE);

        $user = new UserDto();
        $user
            ->setId(self::USER_ONE_SSO_ID)
            ->setEmail('user1.anzu@smeonline.sk')
            ->setPermissionGroups(new ArrayCollection([$permissionGroup]))
        ;

        yield $user;

        $user = new UserDto();
        $user
            ->setId(self::USER_TWO_SSO_ID)
            ->setEmail('user2.anzu@smeonline.sk')
            ->setPermissionGroups(new ArrayCollection([$permissionGroup]))
        ;

        yield $user;
    }

    private function afterPersistAction(User $user): void
    {
        $defaultCmsLicence = $this->baseAssetLicenceFixtures->getOneFromRegistry(
            key: BaseAssetLicenceFixtures::DEFAULT_LICENCE_ID
        );
        $defaultBlogLicence = $this->assetLicenceFixtures->getOneFromRegistry(
            key: AssetLicenceFixtures::BLOG_DEFAULT_ASSET_LICENCE_ID
        );

        switch ($user->getId()) {
            case self::USER_ONE_SSO_ID:
                $user
                    ->setAssetLicences(new ArrayCollection([$defaultCmsLicence]));

                break;
            case self::USER_TWO_SSO_ID:
                $user
                    ->setRoles([User::ROLE_UGC, User::ROLE_USER])
                    ->setAssetLicences(new ArrayCollection([$defaultCmsLicence, $defaultBlogLicence]));

                break;
        }
    }
}
