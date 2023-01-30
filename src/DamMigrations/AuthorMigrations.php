<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Model\Enum\AuthorType;
use AnzuSystems\CoreDamBundle\Model\Enum\PodcastImportMode;
use AnzuSystems\CoreDamBundle\Model\Enum\PodcastLastImportStatus;
use App\App;
use App\Entity\User;
use App\Model\MigrateConfig;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Uid\Uuid;

final class AuthorMigrations extends AbstractMigrations
{
    use OutputUtilTrait;

    public function migrate(MigrateConfig $migrateConfig): void
    {
        $this->outputUtil->info('Migrate authors');
        $progress = $this->outputUtil->createProgressBar($this->totalCount());
        $progress->start();

        foreach ($this->getAuthors() as $row) {
            $this->insertIfNotExists($row);
            $progress->advance();
        }

        $progress->finish();
        $this->outputUtil->writeln('');
    }

    private function totalCount(): int
    {
        return (int) $this->damLegacyConnection->fetchOne('SELECT count(a.id) FROM author a');
    }

    private function insertIfNotExists(array $row): void
    {
        $result = $this->defaultConnection->fetchOne(
            'SELECT id FROM author WHERE name = ?',
            [$row['title']]
        );

        if (false === $result) {
            $this->defaultConnection->insert(
                'author',
                [
                    'id' => Uuid::v6(),
                    'ext_system_id' => self::CMS_EXT_SYSTEM_ID,
                    'created_by_id' => App::getUserIdConsole(),
                    'modified_by_id' => App::getUserIdConsole(),
                    'name' => $row['title'],
                    'identifier' => sprintf('%s (%s)', $row['title'], $row['id']),
                    'type' => AuthorType::Internal->toString(),
                    'created_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                    'modified_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                    'flags_reviewed' => 1,
                ]
            );
        }
    }

    private function getAuthors(): array
    {
        return $this->damLegacyConnection->fetchAllAssociative('
            SELECT 
                id,
                title
            FROM author
        ');
    }
}
