<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230102090251 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD selected_licence_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649116F432A FOREIGN KEY (selected_licence_id) REFERENCES asset_licence (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649116F432A ON user (selected_licence_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649116F432A');
        $this->addSql('DROP INDEX IDX_8D93D649116F432A ON user');
        $this->addSql('ALTER TABLE user DROP selected_licence_id');
    }
}
