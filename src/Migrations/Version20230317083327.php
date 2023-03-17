<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230317083327 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5C6780D085');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C6780D085 FOREIGN KEY (main_file_id) REFERENCES asset_file (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5C6780D085');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C6780D085 FOREIGN KEY (main_file_id) REFERENCES asset_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
