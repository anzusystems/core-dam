<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use App\Domain\PermissionGroup\PermissionGroupManager;
use App\Entity\PermissionGroup;
use App\Security\Permission\DamPermissions;
use App\Security\Permission\Grants;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractFixtures<PermissionGroup>
 */
final class PermissionGroupFixtures extends AbstractFixtures
{
    public const BASIC_GROUP_TITLE = 'DAM Basic';

    public function __construct(
        private readonly PermissionGroupManager $permissionGroupManager,
        private readonly UserFixtures $userFixtures,
    ) {
    }

    public static function getIndexKey(): string
    {
        return PermissionGroup::class;
    }

    public static function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ProgressBar $progressBar): void
    {
        foreach ($progressBar->iterate($this->getData()) as $permissionGroup) {
            $permissionGroup = $this->permissionGroupManager->create($permissionGroup, false);
            $this->addToRegistry($permissionGroup, $permissionGroup->getTitle());
        }
        $this->permissionGroupManager->flush();
    }

    /**
     * @return iterable<PermissionGroup>
     */
    private function getData(): iterable
    {
        $users = new ArrayCollection([
            $this->userFixtures->getOneFromRegistry(UserFixtures::USER_ONE_SSO_ID),
            $this->userFixtures->getOneFromRegistry(UserFixtures::USER_TWO_SSO_ID),
        ]);

        $permissionGroup = new PermissionGroup();
        $permissionGroup
            ->setTitle(self::BASIC_GROUP_TITLE)
            ->setDescription('Basic permission group for DAM access.')
            ->setPermissions(DamPermissions::default(Grants::GRANT_ALLOW))
            ->setUsers($users)
        ;

        yield $permissionGroup;
    }
}
