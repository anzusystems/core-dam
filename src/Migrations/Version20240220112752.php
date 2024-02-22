<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240220112752 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE distribution 
                ADD distribution_asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\',
                ADD distribution_asset_file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\',
                ADD CONSTRAINT FK_A448378120557AAF FOREIGN KEY (distribution_asset_id) REFERENCES asset (id) ON DELETE CASCADE,
                ADD CONSTRAINT FK_A44837814CC89533 FOREIGN KEY (distribution_asset_file_id) REFERENCES asset_file (id) ON DELETE CASCADE
        ');
        $this->addSql('CREATE INDEX IDX_A448378120557AAF ON distribution (distribution_asset_id)');
        $this->addSql('CREATE INDEX IDX_A44837814CC89533 ON distribution (distribution_asset_file_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution DROP FOREIGN KEY FK_A448378120557AAF');
        $this->addSql('ALTER TABLE distribution DROP FOREIGN KEY FK_A44837814CC89533');
        $this->addSql('DROP INDEX IDX_A448378120557AAF ON distribution');
        $this->addSql('DROP INDEX IDX_A44837814CC89533 ON distribution');
        $this->addSql('ALTER TABLE distribution DROP distribution_asset_id, DROP distribution_asset_file_id');
    }
}
