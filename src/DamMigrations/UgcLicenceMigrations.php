<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\Entity\User;
use App\Model\MigrateConfig;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class UgcLicenceMigrations extends AbstractMigrations
{
    /**
     * @throws Exception
     */
    public function migrate(MigrateConfig $migrateConfig): void
    {
        if ($migrateConfig->isNotUgc()) {
            return;
        }
        $res = $this->getGroups();

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount());
        $progressBar->setFormat('debug');
        $progressBar->start();

        while ($row = $res->fetchAssociative()) {
            if ($this->hasLicence($row['id'])) {
                continue;
            }

            $this->insertLicence($row);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->writeln('');
    }

    private function hasLicence(int $licenceId): bool
    {
        $res = $this->defaultConnection->fetchOne(
            'SELECT id FROM asset_licence WHERE id = ?',
            [
                $licenceId,
            ]
        );

        return is_int($res);
    }

    private function totalCount(): int
    {
        return (int) $this->damLegacyConnection->fetchOne(
            'SELECT COUNT(id) FROM licence_group'
        );
    }

    private function insertLicence(array $row): void
    {
        $this->defaultConnection->insert(
            'asset_licence',
            [
                'id' => $row['id'],
                'ext_system_id' => $row['ext_system_id'],
                'ext_id' => $row['ext_id'],
                'name' => $this->getExtSystemName($row),
                'limited_files' => $row['limited'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
            ]
        );
    }

    private function getExtSystemName(array $row): string
    {
        return 'Blog system - ' . $row['id'];
    }

    private function getGroups(): Result
    {
        return $this->damLegacyConnection->executeQuery('
            SELECT 
                id, ext_system_id, ext_id, limited, created_at, modified_at
            FROM licence_group
        ');
    }
}
