<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\Entity\User;
use App\Model\MigrateConfig;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class AdmUserMigrations extends AbstractMigrations
{
    /**
     * @throws Exception
     */
    public function migrate(MigrateConfig $migrateConfig): void
    {
        $res = $this->getDamUsers();

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount());
        $progressBar->setFormat('debug');
        $progressBar->start();

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

    private function totalCount(): int
    {
        return (int) $this->damLegacyConnection->fetchOne('
            SELECT COUNT(id) FROM user WHERE JSON_LENGTH(permissions) > 0 OR roles != JSON_ARRAY(\'ROLE_USER\')
        ');
    }

    private function insertUser(string $email, array $row): void
    {
        $this->defaultConnection->insert(
            'user',
            [
                'id' => $row['id'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
                'roles' => $row['roles'],
                'enabled' => $row['enabled'],
                'email' => $email,
                'first_name' => '',
                'last_name' => '',
                'api_token' => $row['api_token'],
                'permissions' => '{}', // TODO
                'allowed_asset_external_providers' => '[]',
                'allowed_distribution_services' => '[]',
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
        }
    }

    private function getDamUsers(): Result
    {
        return $this->damLegacyConnection->executeQuery('
            SELECT id, created_at, modified_at, roles, permissions, enabled, api_token, ext_system_id
            FROM user WHERE JSON_LENGTH(permissions) > 0 OR roles != JSON_ARRAY(\'ROLE_USER\')
        ');
    }
}
