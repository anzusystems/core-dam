<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230413140205 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649116F432A');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649116F432A FOREIGN KEY (selected_licence_id) REFERENCES asset_licence (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649116F432A');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649116F432A FOREIGN KEY (selected_licence_id) REFERENCES asset_licence (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
