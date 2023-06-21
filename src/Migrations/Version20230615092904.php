<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230615092904 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE asset_file 
                ADD asset_attributes_origin_storage VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:OriginStorageType)\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE asset_file 
                DROP asset_attributes_origin_storage
        ');
    }
}
