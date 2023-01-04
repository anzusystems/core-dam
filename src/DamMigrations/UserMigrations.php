<?php

declare(strict_types=1);


namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class UserMigrations
{
    use OutputUtilTrait;

    public function __construct(
        private readonly Connection $damLegacyConnection,
        private readonly Connection $defaultConnection,
        private readonly Connection $coreConnection,
        private readonly Connection $blogConnection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $res = $this->getDamUsers();

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount());
        $progressBar->start();

        while ($row = $res->fetchAssociative()) {
            $email = $this->getEmail($row['id']);
            if (null === $email) {
                $this->outputUtil->error(sprintf('User id (%s) missing', $row['id']));
                continue;
            }
            if ($this->hasUser($row['id'])) {
                continue;
            }

            $this->defaultConnection->beginTransaction();
            $userId = $this->insertUser($email, $row);
            $this->insertLicences($userId);
            $this->defaultConnection->commit();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    private function totalCount(): int
    {
        return (int) $this->damLegacyConnection->fetchOne(
            'SELECT count(id) FROM user'
        );
    }

    private function getEmail(int $userId): ?string
    {
        $email =
            $this->blogConnection->fetchOne('SELECT email FROM user where id = ?', [$userId])
            ?? $this->coreConnection->fetchOne('SELECT email FROM user where id = ?', [$userId]);

        return is_string($email) ? $email : null;
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

    private function insertLicences(int $userId): void
    {
        // todo rename user_asset_licence table
        $licences = $this->getLicences($userId);
        foreach ($licences as $licence) {
            $this->defaultConnection->insert(
                'user_asset_licence',
                [
                    'user_id' => $userId,
                    'asset_licence_id' => $licence['licence_group_id']
                ]
            );
        }
    }

    private function insertUser(string $email, array $row): int
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
                'api_token' => '',
                'permissions' => '[]',
                'allowed_asset_external_providers' => '[]',
                'allowed_distribution_services' => '[]',

            ]
        );

         return (int) $this->defaultConnection->lastInsertId('user');
    }

    private function getLicences(int $userId): array
    {
        return $this->damLegacyConnection->executeQuery('
            SELECT
               licence_group_id,
               user_id
            FROM licence_group_has_user
            WHERE user_id = ?',
            [$userId]
        )->fetchAllAssociative();
    }

    private function getDamUsers(): Result
    {
        return $this->damLegacyConnection->executeQuery('
            SELECT
               id, created_at, modified_at, roles, permissions, enabled, api_token, ext_system_id, limited
            FROM user
        ');
    }
}