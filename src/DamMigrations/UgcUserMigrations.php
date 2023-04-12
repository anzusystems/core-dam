<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\Entity\User;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class UgcUserMigrations extends AbstractMigrations
{
    /**
     * @throws Exception
     */
    public function migrate(): void
    {
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
        return (int) $this->damLegacyConnection->fetchOne('
            SELECT COUNT(u.id) 
            FROM `user` u
            INNER JOIN licence_group_has_user lghu ON lghu.user_id = u.id
            INNER JOIN licence_group lg ON lg.id = lghu.licence_group_id
            WHERE lg.ext_system_id = :extSystemId
        ',
            [
                'extSystemId' => self::BLOG_EXT_SYSTEM_ID,
            ]
        );
    }

    private function insertUser(string $email, array $row, array $licenceIds): void
    {
        if ($this->hasUser($row['id'])) {
            $this->defaultConnection->executeQuery('
                UPDATE `user` 
                SET roles = JSON_ARRAY_APPEND(roles, "$", "ROLE_UGC") 
                WHERE id = ? AND JSON_CONTAINS(roles, \'"ROLE_ADMIN"\', "$") = 0 AND JSON_CONTAINS(roles, \'"ROLE_UGC"\', "$") = 0
            ', [$row['id']]);

            return;
        }

        $this->defaultConnection->insert(
            'user',
            [
                'id' => $row['id'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
                'roles' => json_encode(['ROLE_UGC']),
                'enabled' => $row['enabled'],
                'email' => $email,
                'person_first_name' => '',
                'person_last_name' => '',
                'person_full_name' => '',
                'avatar_color' => '',
                'avatar_text' => '',
                'api_token' => null,
                'permissions' => '{}',
                'allowed_asset_external_providers' => '[]',
                'allowed_distribution_services' => '[]',
                'selected_licence_id' => $licenceIds[0] ?? null,
            ]
        );
    }

    private function insertLicences(int $userId, array $licenceIds): void
    {
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
        return $this->damLegacyConnection->executeQuery(
            '
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
            SELECT u.id, u.created_at, u.modified_at, u.roles, u.permissions, u.enabled, u.api_token, u.ext_system_id 
            FROM `user` u
            INNER JOIN licence_group_has_user lghu ON lghu.user_id = u.id
            INNER JOIN licence_group lg ON lg.id = lghu.licence_group_id
            WHERE lg.ext_system_id = :extSystemId
        ',
            [
                'extSystemId' => self::BLOG_EXT_SYSTEM_ID,
            ]
        );
    }
}
