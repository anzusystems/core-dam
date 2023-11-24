<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231109115851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updates legacy records to be public';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE asset_file set flags_public = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE asset_file set flags_public = 0');
    }
}
