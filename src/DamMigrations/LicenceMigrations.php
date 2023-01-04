<?php

declare(strict_types=1);


namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Model\Enum\PodcastImportMode;
use AnzuSystems\CoreDamBundle\Model\Enum\PodcastLastImportStatus;
use App\App;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Uid\Uuid;

final class LicenceMigrations extends AbstractMigrations
{
    use OutputUtilTrait;

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $res = $this->getGroups();

        $progressBar = $this->outputUtil->createProgressBar($this->totalCount());
        $progressBar->start();

        while ($row = $res->fetchAssociative())
        {
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
                $licenceId
            ]
        );

        return is_int($res);
    }

    private function totalCount(): int
    {
        return (int) $this->damLegacyConnection->fetchOne(
            'SELECT count(id) FROM licence_group'
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
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE
            ]
        );
    }

    private function getExtSystemName(array $row): string
    {
        if (4 === $row['ext_system_id']) {
            return 'Blog system - '. $row['id'];
        }

        return $row['ext_system_id'] . ' - '. $row['ext_id'];
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