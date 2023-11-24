<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231019104617 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE asset_file
                ADD flags_public TINYINT(1) NOT NULL DEFAULT 1, 
                ADD flags_single_use TINYINT(1) NOT NULL DEFAULT 0
        ');
        $this->addSql('
            ALTER TABLE asset_file
                ALTER flags_public DROP DEFAULT,
                ALTER flags_single_use DROP DEFAULT
        ');
        $this->addSql('
            ALTER TABLE custom_form_element 
                ADD attributes_readonly TINYINT(1) NOT NULL DEFAULT 0,
                ADD property VARCHAR(255) NOT NULL 
            ');
        $this->addSql('
            ALTER TABLE custom_form_element
                ALTER attributes_readonly DROP DEFAULT
        ');
        $this->addSql('
            ALTER TABLE image_file
                ADD image_attributes_animated TINYINT(1) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_file DROP flags_public, DROP flags_single_use');
        $this->addSql('ALTER TABLE custom_form_element DROP attributes_readonly, DROP property');
        $this->addSql('ALTER TABLE image_file DROP image_attributes_animated');
    }
}
