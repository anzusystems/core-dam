<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231019105238 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
//        $this->addSql('ALTER TABLE custom_form_element DROP key_name');
        $this->addSql('ALTER TABLE asset_file ADD asset_attributes_convert_to_mime VARCHAR(32) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_form_element ADD key_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE asset_file DROP asset_attributes_convert_to_mime');
    }
}
