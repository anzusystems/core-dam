<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\AssetLicenceFixtures as BaseAssetLicenceFixtures;
use App\Domain\User\UserManager;
use App\Entity\User;
use App\Model\Domain\User\CreateUserDto;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Helper\ProgressBar;

final class UserFixtures extends AbstractFixtures
{
    public const USER_ONE_SSO_ID = 10_001_040;
    public const USER_TWO_SSO_ID = 10_001_043;

    public function __construct(
        private readonly UserManager $userManager,
        private readonly AssetLicenceFixtures $assetLicenceFixtures,
        private readonly BaseAssetLicenceFixtures $baseAssetLicenceFixtures,
    ) {
    }

    public static function getIndexKey(): string
    {
        return User::class;
    }

    public static function getDependencies(): array
    {
        return [AssetLicenceFixtures::class];
    }

    public function useCustomId(): bool
    {
        return true;
    }

    public function load(ProgressBar $progressBar): void
    {
        foreach ($progressBar->iterate($this->getData()) as $createUser) {
            $createUser = $this->userManager->createFromDto($createUser, false);
            $this->afterPersistAction($createUser);
            $this->addToRegistry($createUser, $createUser->getId());
        }
        $this->userManager->flush();
    }

    /**
     * @return iterable<int, CreateUserDto>
     */
    private function getData(): iterable
    {
        $defaultCmsLicence = $this->baseAssetLicenceFixtures->getOneFromRegistry(
            key: BaseAssetLicenceFixtures::DEFAULT_LICENCE_ID
        );
        $defaultBlogLicence = $this->assetLicenceFixtures->getOneFromRegistry(
            key: AssetLicenceFixtures::BLOG_DEFAULT_ASSET_LICENCE_ID
        );

        $user = new CreateUserDto();
        $user
            ->setId(self::USER_ONE_SSO_ID)
            ->setFirstName('User 1.')
            ->setLastName('Anzu')
            ->setEmail('user1.anzu@smeonline.sk')
            ->setAssetLicences(new ArrayCollection([$defaultCmsLicence]))
        ;

        yield $user;

        $user = new CreateUserDto();
        $user
            ->setId(self::USER_TWO_SSO_ID)
            ->setFirstName('User 2.')
            ->setLastName('Anzu')
            ->setEmail('user2.anzu@smeonline.sk')
            ->setAssetLicences(new ArrayCollection([$defaultCmsLicence, $defaultBlogLicence]))
        ;

        yield $user;
    }

    private function afterPersistAction(User $user): void
    {
        switch ($user->getId()) {
            case self::USER_ONE_SSO_ID:
                break;
            case self::USER_TWO_SSO_ID:
                $user
                    ->setRoles([User::ROLE_UGC, User::ROLE_USER]);
                break;
        }
    }
}
