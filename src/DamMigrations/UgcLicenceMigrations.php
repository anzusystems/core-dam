<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\Entity\User;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class UgcLicenceMigrations extends AbstractMigrations
{
    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $res = $this->getGroups();

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount());
        $progressBar->setFormat('debug');
        $progressBar->start();

        $i = 0;
        while ($row = $res->fetchAssociative()) {
            $i++;
            if ($this->hasLicence($row['id'])) {
                continue;
            }

            $this->insertLicence($row);

            if (0 === $i % self::BULK_SIZE) {
                $this->flush();
            }
            $progressBar->advance();
        }

        $this->flush();

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
        $this->prepareBulkInsert(
            'asset_licence',
            [
                'id' => $row['id'],
                'ext_system_id' => $row['ext_system_id'],
                'ext_id' => $row['ext_id'],
                'name' => 'Blog system - ' . $row['ext_id'],
                'limited_files' => $row['limited'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
            ]
        );
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
