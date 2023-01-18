<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\Entity\User;
use App\Model\MigrateConfig;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class UgcUserMigrations extends AbstractMigrations
{
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
            $licenceIds = $this->getLicenceIds($row['id']);
            $this->defaultConnection->beginTransaction();
            $this->insertUser($email, $row, $licenceIds);
            $this->insertLicences($row['id'], $licenceIds);
            $this->defaultConnection->commit();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    private function totalCount(): int
    {
        return (int) $this->damLegacyConnection->fetchOne('SELECT COUNT(id) FROM user');
    }

    private function insertUser(string $email, array $row, array $licenceIds): void
    {
        if ($this->hasUser($row['id'])) {
            $this->defaultConnection->executeQuery('
                UPDATE `user` 
                SET roles = JSON_ARRAY_APPEND(roles, "$", "ROLE_UGC") 
                WHERE sso_id = ? AND JSON_CONTAINS(roles, \'"ROLE_ADMIN"\', "$") = 0 AND JSON_CONTAINS(roles, \'"ROLE_UGC"\', "$") = 0
            ', [$row['id']]);

            return;
        }

        $this->defaultConnection->insert(
            'user',
            [
                'sso_id' => $row['id'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
                'roles' => json_encode(['ROLE_UGC']),
                'enabled' => $row['enabled'],
                'email' => $email,
                'first_name' => '',
                'last_name' => '',
                'api_token' => null,
                'permissions' => '{}',
                'allowed_asset_external_providers' => '[]',
                'allowed_distribution_services' => '[]',
                'selected_licence_id' => $licenceIds[0] ?? null,
            ]
        );

        $this->userIdBySsoIdCache[(int) $row['id']] ??= (int) $this->defaultConnection->lastInsertId('user');
    }

    private function insertLicences(int $ssoUserId, array $licenceIds): void
    {
        $userId = $this->getUserIdBySsoId($ssoUserId);
        foreach ($licenceIds as $licenceId) {
            $this->defaultConnection->executeQuery(
                'INSERT INTO user_asset_licence (user_id, asset_licence_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE user_id = user_id',
                [$userId, $licenceId]
            );
        }

        $this->defaultConnection->executeQuery(
            'INSERT INTO users_to_ext_systems (user_id, ext_system_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE user_id = user_id',
            [$userId, self::BLOG_EXT_SYSTEM_ID]
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
            SELECT id, created_at, modified_at, roles, permissions, enabled, api_token, ext_system_id FROM user
        ');
    }
}
