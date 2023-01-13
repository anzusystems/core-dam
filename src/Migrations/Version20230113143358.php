<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230113143358 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset CHANGE asset_flags_generated_by_system asset_flags_generated_by_system TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE asset_licence ADD limited_files TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset CHANGE asset_flags_generated_by_system asset_flags_generated_by_system TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE asset_licence DROP limited_files');
    }
}
