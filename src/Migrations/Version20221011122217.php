<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221011122217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset_file ADD notify_to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE asset_file ADD CONSTRAINT FK_68FBF3D812433204 FOREIGN KEY (notify_to_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_68FBF3D812433204 ON asset_file (notify_to_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset_file DROP FOREIGN KEY FK_68FBF3D812433204');
        $this->addSql('DROP INDEX IDX_68FBF3D812433204 ON asset_file');
        $this->addSql('ALTER TABLE asset_file DROP notify_to_id');
    }
}
