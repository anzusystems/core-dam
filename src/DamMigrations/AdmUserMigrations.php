<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\Contracts\Security\Grant;
use AnzuSystems\CoreDamBundle\Security\Permission\DamPermissions;
use App\App;
use App\Entity\User;
use App\Model\MigrateConfig;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class AdmUserMigrations extends AbstractMigrations
{
    private const DEFAULT_GROUP_ID = 1;

    private const FULL_DAM_ACCESS_IDS = [
        2209463, 2094837, 2074670, 2058535, 1966165, 1825192, 1777852, 1609469, 1492017, 1484934, 1439952, 1439940,
        773176, 734566, 663606,
    ];

    /**
     * @throws Exception
     */
    public function migrate(MigrateConfig $migrateConfig): void
    {
        $res = $this->getDamUsers();

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount());
        $progressBar->setFormat('debug');
        $progressBar->start();

        $this->createPermissionGroup();

        while ($row = $res->fetchAssociative()) {
            $email = $this->getEmail($row['id']);
            if ($this->hasUser($row['id'])) {
                continue;
            }

            $this->defaultConnection->beginTransaction();
            $this->insertUser($email, $row);
            $this->insertLicences($row);
            $this->defaultConnection->commit();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    protected function hasPermissionGroupGroup(): bool
    {
        $res = $this->defaultConnection->fetchOne(
            'SELECT id FROM permission_group WHERE id = ?',
            [
                self::DEFAULT_GROUP_ID,
            ]
        );

        return is_int($res);
    }

    private function totalCount(): int
    {
        return (int) $this->damLegacyConnection->fetchOne('
            SELECT COUNT(id) FROM user WHERE JSON_LENGTH(permissions) > 0 OR roles != JSON_ARRAY(\'ROLE_USER\')
        ');
    }

    private function insertUser(string $email, array $row): void
    {
        $roles = json_decode($row['roles'], true);

        if (1 === $row['enabled'] && in_array('ROLE_USER', $roles, true)) {
            $userKey = array_search('ROLE_USER', $roles, true);
            if (is_int($userKey)) {
                unset($roles[$userKey]);
            }
            $roles[] = 'ROLE_DAM_ADMIN';
        }

        $this->defaultConnection->insert(
            'user',
            [
                'id' => $row['id'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
                'roles' => json_encode(array_values($roles)),
                'enabled' => $row['enabled'],
                'email' => $email,
                'person_first_name' => '',
                'person_last_name' => '',
                'person_full_name' => '',
                'avatar_color' => '',
                'avatar_text' => '',
                'api_token' => $row['api_token'],
                'permissions' => '{}',
                'allowed_asset_external_providers' => $this->hasFullAccess($row['id'])
                    ? json_encode(['unsplash_cms'])
                    : '[]',
                'allowed_distribution_services' => $this->hasFullAccess($row['id'])
                    ? json_encode(['youtube_cms_main', 'jw_cms', 'artemis_podcast_cms', 'artemis_video_cms'])
                    : '[]',
            ]
        );
    }

    private function insertLicences(array $userRow): void
    {
        $roles = json_decode($userRow['roles'], true);
        $permissions = json_decode($userRow['permissions'], true);
        if (in_array(User::ROLE_USER, $roles, true) && $permissions) {
            $this->defaultConnection->insert(
                'user_asset_licence',
                [
                    'user_id' => $userRow['id'],
                    'asset_licence_id' => self::CMS_LICENCE_ID,
                ]
            );
            $this->defaultConnection->insert(
                'users_to_ext_systems',
                [
                    'user_id' => $userRow['id'],
                    'ext_system_id' => 1,
                ]
            );

            if ($this->hasFullAccess($userRow['id'])) {
                $this->defaultConnection->insert(
                    'admins_to_ext_systems',
                    [
                        'user_id' => $userRow['id'],
                        'ext_system_id' => self::CMS_EXT_SYSTEM_ID,
                    ]
                );
                $this->defaultConnection->insert(
                    'user_permission_group',
                    [
                        'user_id' => $userRow['id'],
                        'permission_group_id' => self::DEFAULT_GROUP_ID,
                    ]
                );
            }
        }
    }

    private function hasFullAccess(int $id): bool
    {
        return in_array($id, self::FULL_DAM_ACCESS_IDS, true);
    }

    private function getDamUsers(): Result
    {
        return $this->damLegacyConnection->executeQuery('
            SELECT id, created_at, modified_at, roles, permissions, enabled, api_token, ext_system_id
            FROM user WHERE JSON_LENGTH(permissions) > 0 OR roles != JSON_ARRAY(\'ROLE_USER\')
        ');
    }

    private function createPermissionGroup(): void
    {
        if ($this->hasPermissionGroupGroup()) {
            return;
        }

        $this->defaultConnection->insert(
            'permission_group',
            [
                'id' => self::DEFAULT_GROUP_ID,
                'title' => 'DAM full access',
                'description' => 'Basic permission group for DAM access.',
                'created_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'modified_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'created_by_id' => App::getUserIdConsole(),
                'modified_by_id' => App::getUserIdConsole(),
                'permissions' => json_encode(DamPermissions::default(Grant::ALLOW)),
            ]
        );
    }
}
