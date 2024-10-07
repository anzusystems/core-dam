<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240925060242 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset_file CHANGE asset_attributes_mime_type asset_attributes_mime_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE chunk CHANGE mime_type mime_type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset_file CHANGE asset_attributes_mime_type asset_attributes_mime_type VARCHAR(127) NOT NULL');
        $this->addSql('ALTER TABLE chunk CHANGE mime_type mime_type VARCHAR(32) NOT NULL');
    }
}
