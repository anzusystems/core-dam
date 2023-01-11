<?php

declare(strict_types=1);


namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\Entity\User;
use App\Model\MigrateConfig;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class UgcUserMigrations extends AbstractMigrations
{
    use OutputUtilTrait;

    /**
     * @throws Exception
     */
    public function migrate(MigrateConfig $migrateConfig): void
    {
        if ($migrateConfig->isNotUgc()) {
            return;
        }

        $res = $this->getDamUsers();

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount());
        $progressBar->setFormat('debug');
        $progressBar->start();

        while ($row = $res->fetchAssociative()) {
            $email = $this->getEmail($row['id']);
            if (null === $email) {
                $this->outputUtil->error(sprintf('User id (%s) missing', $row['id']));
                continue;
            }

            $licences = $this->getLicenceIds($row['id']);
            $this->defaultConnection->beginTransaction();
            if (false === $this->hasUser($row['id'])) {
                $this->insertUser($email, $row);
            }
            $this->insertLicences($row['id'], $licences);
            $this->defaultConnection->commit();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    private function totalCount(): int
    {
        return (int) $this->damLegacyConnection->fetchOne('
            SELECT COUNT(id) FROM user WHERE JSON_LENGTH(permissions) = 0 AND roles = JSON_ARRAY(\'ROLE_USER\')
        ');
    }

    private function getEmail(int $userId): ?string
    {
        $email =
            $this->blogConnection->fetchOne('SELECT email FROM user where id = ?', [$userId])
            ?? $this->coreConnection->fetchOne('SELECT email FROM user where id = ?', [$userId]);

        return is_string($email) ? $email : 'dam-' . $userId . '@anzusystems.dev'; // TODO remove
    }

    private function hasUser(int $userId): bool
    {
        $res = $this->defaultConnection->fetchOne(
            'SELECT id FROM user WHERE sso_id = ?',
            [
                $userId
            ]
        );

        return is_int($res);
    }

    private function insertUser(string $email, array $row): void
    {
        $this->defaultConnection->insert(
            'user',
            [
                'sso_id' => $row['id'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
                'roles' => $row['roles'],
                'enabled' => $row['enabled'],
                'email' => $email,
                'first_name' => '',
                'last_name' => '',
                'api_token' => null,
                'permissions' => '{}',
                'allowed_asset_external_providers' => '[]',
                'allowed_distribution_services' => '[]',
            ]
        );

        $this->userIdBySsoIdCache[(int) $row['id']] ??= (int) $this->defaultConnection->lastInsertId('user');
    }

    private function insertLicences(int $ssoUserId, array $licenceIds): void
    {
        $userId = $this->getUserIdBySsoId($ssoUserId);
        foreach ($licenceIds as $licenceId) {
            $this->defaultConnection->insert(
                'user_asset_licence',
                [
                    'user_id' => $userId,
                    'asset_licence_id' => $licenceId,
                ]
            );
        }

        $this->defaultConnection->insert(
            'users_to_ext_systems',
            [
                'user_id' => $userId,
                'ext_system_id' => self::BLOG_EXT_SYSTEM_ID,
            ]
        );
    }

    /**
     * @return list<int>
     */
    private function getLicenceIds(int $sooUserId): array
    {
        return $this->damLegacyConnection->executeQuery('
            SELECT
               licence_group_id
            FROM licence_group_has_user
            WHERE user_id = ?',
            [$sooUserId]
        )->fetchFirstColumn();
    }

    private function getDamUsers(): Result
    {
        return $this->damLegacyConnection->executeQuery('
            SELECT id, created_at, modified_at, roles, permissions, enabled, api_token, ext_system_id
            FROM user WHERE JSON_LENGTH(permissions) = 0 AND roles = JSON_ARRAY(\'ROLE_USER\')
        ');
    }
}
