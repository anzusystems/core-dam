<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231205153256 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE asset_file_route 
            (
                id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', 
                target_asset_file_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', 
                created_by_id INT DEFAULT NULL, 
                modified_by_id INT DEFAULT NULL, 
                status VARCHAR(255) NOT NULL, 
                mode VARCHAR(255) NOT NULL, 
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                uri_path VARCHAR(255) NOT NULL, 
                uri_slug VARCHAR(255) NOT NULL, 
                uri_main TINYINT(1) NOT NULL, 
                INDEX IDX_6EA67314ECA90B8B (target_asset_file_id), 
                INDEX IDX_6EA67314B03A8386 (created_by_id), 
                INDEX IDX_6EA6731499049ECE (modified_by_id), 
                INDEX IDX_main_asset_file_id (uri_main, target_asset_file_id),
                UNIQUE INDEX UNIQ_uri_path (uri_path),
                PRIMARY KEY(id),
                CONSTRAINT FK_6EA67314ECA90B8B FOREIGN KEY (target_asset_file_id) REFERENCES asset_file (id),
                CONSTRAINT FK_6EA67314B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id),
                CONSTRAINT FK_6EA6731499049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)
            ) 
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE asset_file 
                ADD main_route_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\',
                ADD CONSTRAINT FK_68FBF3D8EDCEA593 FOREIGN KEY (main_route_id) REFERENCES asset_file_route (id) ON DELETE SET NULL,
                CHANGE asset_attributes_mime_type asset_attributes_mime_type VARCHAR(127) NOT NULL, 
                CHANGE asset_attributes_convert_to_mime asset_attributes_convert_to_mime VARCHAR(127) NOT NULL
        ');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68FBF3D8EDCEA593 ON asset_file (main_route_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_file_route DROP FOREIGN KEY FK_6EA67314ECA90B8B');
        $this->addSql('ALTER TABLE asset_file_route DROP FOREIGN KEY FK_6EA67314B03A8386');
        $this->addSql('ALTER TABLE asset_file_route DROP FOREIGN KEY FK_6EA6731499049ECE');
        $this->addSql('DROP TABLE asset_file_route');

        $this->addSql('ALTER TABLE asset_file 
            CHANGE asset_attributes_mime_type asset_attributes_mime_type VARCHAR(32) NOT NULL, 
            CHANGE asset_attributes_convert_to_mime asset_attributes_convert_to_mime VARCHAR(32) NOT NULL
        ');
    }
}
