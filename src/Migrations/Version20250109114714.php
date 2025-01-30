<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250109114714 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset ADD sibling_to_asset_id CHAR(36) DEFAULT NULL');
        $this->addSql('SET SESSION foreign_key_checks = OFF');
        $this->addSql('
            ALTER TABLE asset
                ADD CONSTRAINT FK_2AF5A5CCA830096 FOREIGN KEY (sibling_to_asset_id) REFERENCES asset (id),
                ALGORITHM = INPLACE,
                LOCK = NONE
        ');
        $this->addSql('SET SESSION foreign_key_checks = ON');
        $this->addSql('CREATE INDEX IDX_2AF5A5CCA830096 ON asset (sibling_to_asset_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5CCA830096');
        $this->addSql('DROP INDEX IDX_2AF5A5CCA830096 ON asset');
        $this->addSql('ALTER TABLE asset DROP sibling_to_asset_id');
    }
}
