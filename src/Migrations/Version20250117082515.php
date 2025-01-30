<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250117082515 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();

        $this->addSql(
            "INSERT INTO `public_export` (id, `slug`, `type`, created_by_id, modified_by_id, created_at, modified_at, ext_system_id, asset_licence_id) VALUES
            (1, 'cms-mobile', 'mobile', '{$idConsole}', '{$idConsole}', NOW(), NOW(), 1, 100000),
            (2, 'cms-web', 'web', '{$idConsole}', '{$idConsole}', NOW(), NOW(), 1, 100000)
        "
        );
        $this->addSql(
            'UPDATE `podcast_episode` SET attributes_web_order_position = position * 10, attributes_mobile_order_position = position * 10
        '
        );
        $this->addSql("
            UPDATE podcast_episode pe
                INNER JOIN asset ass ON ass.id = pe.asset_id
            SET pe.flags_mobile_public_export_enabled = 1, pe.flags_web_public_export_enabled = 1
            WHERE (pe.flags_from_rss = 1 AND JSON_CONTAINS(ass.asset_file_properties_slot_names, '\"free\"')) OR pe.attributes_ext_url <> ''
        ");
        $this->addSql('
            UPDATE podcast_episode pe
                INNER JOIN podcast p ON p.id = pe.podcast_id
            SET pe.licence_id = p.licence_id
            WHERE p.licence_id is not null
        ');
        $this->addSql("
            UPDATE podcast_episode pe
            INNER JOIN asset ass ON pe.asset_id = ass.id
            INNER JOIN asset_slot asl ON ass.id = asl.asset_id
            INNER JOIN audio_file af ON asl.audio_id = af.id AND asl.name = 'free'
            SET pe.attributes_duration = af.attributes_duration
        ");

        $this->addSql("UPDATE podcast set attributes_web_order_position = 100,attributes_mobile_order_position = 100, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d704-628c-8d2d-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 200,attributes_mobile_order_position = 200, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d79c-6eb0-bc1e-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 500,attributes_mobile_order_position = 500, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d734-6b3a-8bee-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 400,attributes_mobile_order_position = 500, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d760-6028-b363-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 300,attributes_mobile_order_position = 300, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d7cd-6f56-9780-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 700,attributes_mobile_order_position = 800, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d814-6eb0-85cf-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1300,attributes_mobile_order_position = 900, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-da42-6aca-8fdb-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 800,attributes_mobile_order_position = 600, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-db15-63c6-8796-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 900,attributes_mobile_order_position = 400, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dbe6-6b38-a952-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1000,attributes_mobile_order_position = 1400, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d9f7-6d4a-9f39-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1700,attributes_mobile_order_position = 1100, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dd32-6c6c-9d5b-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 2000,attributes_mobile_order_position = 2000, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dd96-63d4-83b8-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1900,attributes_mobile_order_position = 1900, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dd05-6474-a958-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1100,attributes_mobile_order_position = 1100, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 0 WHERE id = '1edc17e7-db96-6ab6-ae7c-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1400,attributes_mobile_order_position = 1400, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-db58-686a-b43f-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1600,attributes_mobile_order_position = 1600, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-ddc6-64bc-9ce7-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1800,attributes_mobile_order_position = 1300, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d9a9-64c4-bf1c-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 2100,attributes_mobile_order_position = 2800, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dc26-67d8-95dd-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1500,attributes_mobile_order_position = 700, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-e33e-625a-bc18-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1300,attributes_mobile_order_position = 1300, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-e36c-6376-98c0-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 2200,attributes_mobile_order_position = 2900, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dae2-6e58-bd85-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1200,attributes_mobile_order_position = 1200, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d94b-6342-8be6-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 600,attributes_mobile_order_position = 1300, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-d875-6d82-a4a0-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1100,attributes_mobile_order_position = 1100, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-da89-681c-bb8e-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 2600,attributes_mobile_order_position = 1200, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dd6f-6bbc-8879-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 2400,attributes_mobile_order_position = 2100, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dc6e-6d80-83b8-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 2500,attributes_mobile_order_position = 2500, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-e49b-6166-8320-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 2700,attributes_mobile_order_position = 2400, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dca7-6022-bf8d-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 2800,attributes_mobile_order_position = 2800, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 0 WHERE id = '1edc17e7-e4ea-60a4-9f31-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 2900,attributes_mobile_order_position = 2900, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-e518-6da0-a12a-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3000,attributes_mobile_order_position = 2200, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-e565-6196-88a2-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3100,attributes_mobile_order_position = 2300, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-de13-6366-a8e7-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3200,attributes_mobile_order_position = 1900, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-e543-606e-9e21-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3300,attributes_mobile_order_position = 2000, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-e5b1-6172-b0db-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3400,attributes_mobile_order_position = 700, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-e5f3-62c0-87a1-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1300,attributes_mobile_order_position = 1000, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-dcd4-68ce-90b4-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 700,attributes_mobile_order_position = 700, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1edc17e7-de71-69fc-b003-3126b6164b0b'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1900,attributes_mobile_order_position = 1900, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1ee67724-5fdd-65ae-8301-6d7317f6cc16'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3000,attributes_mobile_order_position = 500, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1ee6e5e1-cc24-6eb6-a2ec-49751a0947c4'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1500,attributes_mobile_order_position = 900, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1eedfbf1-c6e3-61aa-a123-5969694536b9'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3850,attributes_mobile_order_position = 1600, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1eef26d4-fce0-6c62-9970-1b56daac1f76'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3750,attributes_mobile_order_position = 1700, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1eef5910-5b98-60e4-a41b-0770c93b24c1'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3800,attributes_mobile_order_position = 1800, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1eef5914-019c-610e-a465-3fa4191ae9e1'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 3700,attributes_mobile_order_position = 1900, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1eef5916-9b6a-61f8-a720-d54114e377d8'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 350,attributes_mobile_order_position = 350, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1ef74c24-75bf-60e4-9f2e-bb216529f188'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 450,attributes_mobile_order_position = 450, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1ef808be-db06-6ca2-b417-6f0b206d0493'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 800,attributes_mobile_order_position = 800, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1ef9852d-9ffd-6fec-8acd-f3c6bb42473d'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1000,attributes_mobile_order_position = 1200, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1ef9b739-4dec-63be-a975-87da3bb5c1c3'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 400,attributes_mobile_order_position = 400, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1efb2fed-7dec-6400-90be-b7fff44baffb'");
        $this->addSql("UPDATE podcast set attributes_web_order_position = 1400,attributes_mobile_order_position = 1400, flags_web_public_export_enabled = 1, flags_mobile_public_export_enabled = 1 WHERE id = '1efb94fa-63bd-6508-a16e-23059c23a295'");
        $this->addSql('UPDATE podcast set attributes_web_order_position = 5000 WHERE attributes_web_order_position = 0');
        $this->addSql('UPDATE podcast set attributes_mobile_order_position = 5000 WHERE attributes_mobile_order_position = 0');

        $this->addSql('
            SET @i:=0; UPDATE video_show SET attributes_web_order_position = @i:=(@i+100)  
        ');
        $this->addSql('
            SET @i:=0; UPDATE video_show SET attributes_mobile_order_position = @i:=(@i+100) 
        ');
        $this->addSql("
            UPDATE video_show SET flags_web_public_export_enabled = 1 where id IN ('1ef7423a-1a86-67ca-ab02-1ded068ce5f7', '1edd902c-f3de-611e-bb1c-2bf75087e621', '1edd902c-f393-6506-bee1-2bf75087e621', '1edd902c-f3af-654e-b099-2bf75087e621')
        ");
        $this->addSql("
            UPDATE video_show SET flags_mobile_public_export_enabled = 1 where id IN ('1ef7423a-1a86-67ca-ab02-1ded068ce5f7', '1edd902c-f3de-611e-bb1c-2bf75087e621', '1edd902c-f393-6506-bee1-2bf75087e621', '1edd902c-f3af-654e-b099-2bf75087e621')
        ");
        $this->addSql('
            UPDATE video_show_episode SET flags_web_public_export_enabled = 1
        ');
        $this->addSql('
            UPDATE video_show_episode SET flags_mobile_public_export_enabled = 1
        ');
        $this->addSql('
            UPDATE video_show_episode SET attributes_web_order_position = position * 10
        ');
        $this->addSql('
            UPDATE video_show_episode SET attributes_mobile_order_position = position * 10
        ');
        $this->addSql('
            UPDATE video_show_episode SET dates_publication_date = created_at where true
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE from public_export where id in (1, 2)');
    }
}
