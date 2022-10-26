<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221025052251 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE author ADD identifier VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BDAFD8C85E237E06772E836AE961F7A ON author (name, identifier, ext_system_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_BDAFD8C85E237E06772E836AE961F7A ON author');
        $this->addSql('ALTER TABLE author DROP identifier');
    }
}
