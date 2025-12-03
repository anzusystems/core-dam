<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251203084542 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_modified_at ON asset (modified_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_modified_at ON asset');
    }
}
