<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221018140200 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_file_metadata DROP keyword_suggestions, DROP author_suggestions');
        $this->addSql('ALTER TABLE asset_metadata ADD keyword_suggestions JSON NOT NULL, ADD author_suggestions JSON NOT NULL');
        $this->addSql('ALTER TABLE author CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE keyword CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_file_metadata ADD keyword_suggestions JSON NOT NULL, ADD author_suggestions JSON NOT NULL');
        $this->addSql('ALTER TABLE asset_metadata DROP keyword_suggestions, DROP author_suggestions');
        $this->addSql('ALTER TABLE author CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE keyword CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
