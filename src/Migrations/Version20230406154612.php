<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230406154612 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_select_name_value ON distribution_category_option');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_select_name_value ON distribution_category_option (select_id, value)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_select_name_value ON distribution_category_option');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_select_name_value ON distribution_category_option (select_id, name, value)');
    }
}
