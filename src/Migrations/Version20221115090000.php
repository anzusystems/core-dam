<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Factory\UuidFactory;

final class Version20221115090000 extends AbstractMigration
{
    private array $optionIds = [];

    public function up(Schema $schema): void
    {
        $uuidFactory = new UuidFactory();
        $youtubeCmsMainId = (string) $uuidFactory->create();
        $jwCmsId = (string) $uuidFactory->create();
        $artemisCmsId = (string) $uuidFactory->create();

        $this->addSql("
            INSERT INTO distribution_category_select (id, ext_system_id, service_slug, `type`, created_by_id, modified_by_id, created_at, modified_at) VALUES 
            ('{$youtubeCmsMainId}', 1, 'youtube_cms_main', 'video', 1, 1, NOW(), NOW()),
            ('{$jwCmsId}', 1, 'jw_cms', 'video', 1, 1, NOW(), NOW()),
            ('{$artemisCmsId}', 1, 'artemis_cms', 'video', 1, 1, NOW(), NOW());
        ");


        $this->addSql("
            INSERT INTO distribution_category_option (id, select_id, `name`, `value`, assignable, `position`, created_by_id, modified_by_id, created_at, modified_at) VALUES 
            ('{$this->getOptionUuidById('youtube', 1)}', '{$youtubeCmsMainId}', 'Foreign', '38', 0, 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 2)}', '{$youtubeCmsMainId}', 'Comedy', '34', 0, 2, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 3)}', '{$youtubeCmsMainId}', 'Documentary', '35', 0, 3, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 4)}', '{$youtubeCmsMainId}', 'Drama', '36', 0, 4, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 5)}', '{$youtubeCmsMainId}', 'Family', '37', 0, 5, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 6)}', '{$youtubeCmsMainId}', 'Shorts', '42', 0, 6, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 7)}', '{$youtubeCmsMainId}', 'Horror', '39', 0, 7, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 8)}', '{$youtubeCmsMainId}', 'Sci-Fi/Fantasy', '40', 0, 8, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 9)}', '{$youtubeCmsMainId}', 'Thriller', '41', 0, 9, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 10)}', '{$youtubeCmsMainId}', 'Shows', '43', 0, 10, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 11)}', '{$youtubeCmsMainId}', 'Trailers', '44', 0, 11, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 12)}', '{$youtubeCmsMainId}', 'Action/Adventure', '32', 0, 12, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 13)}', '{$youtubeCmsMainId}', 'Classics', '33', 0, 13, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 14)}', '{$youtubeCmsMainId}', 'Movies', '30', 0, 14, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 15)}', '{$youtubeCmsMainId}', 'Anime/Animation', '31', 0, 15, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 16)}', '{$youtubeCmsMainId}', 'Gaming', '20', 1, 16, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 17)}', '{$youtubeCmsMainId}', 'Film & Animation', '1', 1, 17, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 18)}', '{$youtubeCmsMainId}', 'Autos & Vehicles', '2', 1, 18, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 19)}', '{$youtubeCmsMainId}', 'Music', '10', 1, 19, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 20)}', '{$youtubeCmsMainId}', 'Sports', '17', 1, 20, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 21)}', '{$youtubeCmsMainId}', 'Short Movies', '18', 0, 21, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 22)}', '{$youtubeCmsMainId}', 'Travel & Events', '19', 1, 22, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 23)}', '{$youtubeCmsMainId}', 'Pets & Animals', '15', 1, 23, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 24)}', '{$youtubeCmsMainId}', 'Videoblogging', '21', 0, 24, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 25)}', '{$youtubeCmsMainId}', 'Comedy', '23', 1, 25, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 26)}', '{$youtubeCmsMainId}', 'Entertainment', '24', 1, 26, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 27)}', '{$youtubeCmsMainId}', 'News & Politics', '25', 1, 27, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 28)}', '{$youtubeCmsMainId}', 'Howto & Style', '26', 1, 28, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 29)}', '{$youtubeCmsMainId}', 'Education', '27', 1, 29, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 30)}', '{$youtubeCmsMainId}', 'Science & Technology', '28', 1, 30, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('youtube', 31)}', '{$youtubeCmsMainId}', 'People & Blogs', '22', 1, 31, 1, 1, NOW(), NOW());
        ");

        $this->addSql("
            INSERT INTO distribution_category_option (id, select_id, `name`, `value`, assignable, `position`, created_by_id, modified_by_id, created_at, modified_at) VALUES 
            ('{$this->getOptionUuidById('jw', 1)}', '{$jwCmsId}', 'Sports', 'Sports', 1, 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 2)}', '{$jwCmsId}', 'Real Estate', 'Real Estate', 1, 2, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 3)}', '{$jwCmsId}', 'Religion & Spirituality', 'Religion & Spirituality', 1, 3, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 4)}', '{$jwCmsId}', 'Science', 'Science', 1, 4, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 5)}', '{$jwCmsId}', 'Shopping', 'Shopping', 1, 5, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 6)}', '{$jwCmsId}', 'Travel', 'Travel', 1, 6, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 7)}', '{$jwCmsId}', 'Style & Fashion', 'Style & Fashion', 1, 7, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 8)}', '{$jwCmsId}', 'Technology & Computing', 'Technology & Computing', 1, 8, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 9)}', '{$jwCmsId}', 'Television', 'Television', 1, 9, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 10)}', '{$jwCmsId}', 'Video Gaming', 'Video Gaming', 1, 10, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 11)}', '{$jwCmsId}', 'Pets', 'Pets', 1, 11, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 12)}', '{$jwCmsId}', 'Pop Culture', 'Pop Culture', 1, 12, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 13)}', '{$jwCmsId}', 'Personal Finance', 'Personal Finance', 1, 13, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 14)}', '{$jwCmsId}', 'Books and Literature', 'Books and Literature', 1, 14, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 15)}', '{$jwCmsId}', 'Fine Art', 'Fine Art', 1, 15, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 16)}', '{$jwCmsId}', 'Automotive', 'Automotive', 1, 16, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 17)}', '{$jwCmsId}', 'Education', 'Education', 1, 17, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 18)}', '{$jwCmsId}', 'News and Politics', 'News and Politics', 1, 18, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 19)}', '{$jwCmsId}', 'Business and Finance', 'Business and Finance', 1, 19, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 20)}', '{$jwCmsId}', 'Events and Attractions', 'Events and Attractions', 1, 20, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 21)}', '{$jwCmsId}', 'Family and Relationships', 'Family and Relationships', 1, 21, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 22)}', '{$jwCmsId}', 'Careers', 'Careers', 1, 22, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 23)}', '{$jwCmsId}', 'Food & Drink', 'Food & Drink', 1, 23, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 24)}', '{$jwCmsId}', 'Hobbies & Interests', 'Hobbies & Interests', 1, 24, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 25)}', '{$jwCmsId}', 'Home & Garden', 'Home & Garden', 1, 25, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 27)}', '{$jwCmsId}', 'Medical Health', 'Medical Health', 1, 27, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 28)}', '{$jwCmsId}', 'Movies', 'Movies', 1, 28, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 29)}', '{$jwCmsId}', 'Healthy Living', 'Healthy Living', 1, 29, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('jw', 30)}', '{$jwCmsId}', 'Music and Audio', 'Music and Audio', 1, 30, 1, 1, NOW(), NOW());
        ");

        $this->addSql("
            INSERT INTO distribution_category_option (id, select_id, `name`, `value`, assignable, `position`, created_by_id, modified_by_id, created_at, modified_at) VALUES 
            ('{$this->getOptionUuidById('artemis', 1)}', '{$artemisCmsId}', 'Dokumenty', '7025', 1, 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('artemis', 2)}', '{$artemisCmsId}', 'Spravodajstvo', '7026', 1, 2, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('artemis', 3)}', '{$artemisCmsId}', 'Publicistika', '7028', 1, 3, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('artemis', 4)}', '{$artemisCmsId}', 'Zábava', '7031', 1, 4, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('artemis', 5)}', '{$artemisCmsId}', 'Komerčné video', '7057', 1, 5, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('artemis', 6)}', '{$artemisCmsId}', 'PR video', '7058', 1, 6, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('artemis', 7)}', '{$artemisCmsId}', 'Import iné', '7061', 1, 7, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('artemis', 8)}', '{$artemisCmsId}', 'Dokumenty', '6907', 1, 8, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('artemis', 9)}', '{$artemisCmsId}', 'Podcasty', '6978', 1, 9, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('artemis', 10)}', '{$artemisCmsId}', 'Financie bez obalu od A po Z', '7750', 1, 10, 1, 1, NOW(), NOW());
        ");

        $this->addSql("
            INSERT INTO distribution_category (id, `name`, `type`, ext_system_id, created_by_id, modified_by_id, created_at, modified_at) VALUES 
            ('{$this->getOptionUuidById('cat', 11)}', 'Ukážky', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 14)}', 'Film', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 15)}', 'Zvieratká', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 16)}', 'Správy a politika', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 17)}', 'Veda', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 18)}', 'Technológie', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 19)}', 'O filmoch a animáci', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 20)}', 'Autá', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 21)}', 'Hudba', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 22)}', 'Šport', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 23)}', 'Vzdelávanie', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 25)}', 'Cestovanie a podujatia', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 26)}', 'Hry', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 28)}', 'Zábava', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 30)}', 'Návody a štýl', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 31)}', 'Videoblog', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 32)}', 'PR video', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 33)}', 'Komerčné video', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 34)}', 'Dokumenty', 'video', 1, 1, 1, NOW(), NOW()),
            ('{$this->getOptionUuidById('cat', 35)}', 'Publicistika', 'video', 1, 1, 1, NOW(), NOW());
        ");

        $this->addSql("
            INSERT INTO distribution_category_has_selected_option (distribution_category_id, distribution_category_option_id) VALUES 
            ('{$this->getOptionUuidById('cat', 11)}', '{$this->getOptionUuidById('youtube', 17)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 11)}', '{$this->getOptionUuidById('jw', 9)}'),    
            ('{$this->getOptionUuidById('cat', 11)}', '{$this->getOptionUuidById('artemis', 4)}'),
            ('{$this->getOptionUuidById('cat', 14)}', '{$this->getOptionUuidById('youtube', 17)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 14)}', '{$this->getOptionUuidById('jw', 28)}'),    
            ('{$this->getOptionUuidById('cat', 14)}', '{$this->getOptionUuidById('artemis', 4)}'),
            ('{$this->getOptionUuidById('cat', 15)}', '{$this->getOptionUuidById('youtube', 23)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 15)}', '{$this->getOptionUuidById('jw', 11)}'),    
            ('{$this->getOptionUuidById('cat', 15)}', '{$this->getOptionUuidById('artemis', 4)}'),
            ('{$this->getOptionUuidById('cat', 16)}', '{$this->getOptionUuidById('youtube', 27)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 16)}', '{$this->getOptionUuidById('jw', 18)}'),    
            ('{$this->getOptionUuidById('cat', 16)}', '{$this->getOptionUuidById('artemis', 2)}'),
            ('{$this->getOptionUuidById('cat', 17)}', '{$this->getOptionUuidById('youtube', 30)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 17)}', '{$this->getOptionUuidById('jw', 4)}'),    
            ('{$this->getOptionUuidById('cat', 17)}', '{$this->getOptionUuidById('artemis', 2)}'),
            ('{$this->getOptionUuidById('cat', 18)}', '{$this->getOptionUuidById('youtube', 30)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 18)}', '{$this->getOptionUuidById('jw', 4)}'),    
            ('{$this->getOptionUuidById('cat', 18)}', '{$this->getOptionUuidById('artemis', 2)}'),
            ('{$this->getOptionUuidById('cat', 19)}', '{$this->getOptionUuidById('youtube', 17)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 19)}', '{$this->getOptionUuidById('jw', 28)}'),    
            ('{$this->getOptionUuidById('cat', 19)}', '{$this->getOptionUuidById('artemis', 2)}'),
            ('{$this->getOptionUuidById('cat', 20)}', '{$this->getOptionUuidById('youtube', 18)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 20)}', '{$this->getOptionUuidById('jw', 16)}'),    
            ('{$this->getOptionUuidById('cat', 20)}', '{$this->getOptionUuidById('artemis', 2)}'),
            ('{$this->getOptionUuidById('cat', 21)}', '{$this->getOptionUuidById('youtube', 19)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 21)}', '{$this->getOptionUuidById('jw', 30)}'),    
            ('{$this->getOptionUuidById('cat', 21)}', '{$this->getOptionUuidById('artemis', 1)}'),
            ('{$this->getOptionUuidById('cat', 22)}', '{$this->getOptionUuidById('youtube', 20)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 22)}', '{$this->getOptionUuidById('jw', 1)}'),    
            ('{$this->getOptionUuidById('cat', 22)}', '{$this->getOptionUuidById('artemis', 2)}'),
            ('{$this->getOptionUuidById('cat', 23)}', '{$this->getOptionUuidById('youtube', 29)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 23)}', '{$this->getOptionUuidById('jw', 17)}'),    
            ('{$this->getOptionUuidById('cat', 23)}', '{$this->getOptionUuidById('artemis', 1)}'),
            ('{$this->getOptionUuidById('cat', 25)}', '{$this->getOptionUuidById('youtube', 22)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 25)}', '{$this->getOptionUuidById('jw', 6)}'),    
            ('{$this->getOptionUuidById('cat', 25)}', '{$this->getOptionUuidById('artemis', 1)}'),
            ('{$this->getOptionUuidById('cat', 26)}', '{$this->getOptionUuidById('youtube', 16)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 26)}', '{$this->getOptionUuidById('jw', 10)}'),    
            ('{$this->getOptionUuidById('cat', 26)}', '{$this->getOptionUuidById('artemis', 4)}'),
            ('{$this->getOptionUuidById('cat', 28)}', '{$this->getOptionUuidById('youtube', 26)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 28)}', '{$this->getOptionUuidById('artemis', 4)}'),
            ('{$this->getOptionUuidById('cat', 30)}', '{$this->getOptionUuidById('youtube', 28)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 30)}', '{$this->getOptionUuidById('jw', 24)}'),    
            ('{$this->getOptionUuidById('cat', 30)}', '{$this->getOptionUuidById('artemis', 1)}'),
            ('{$this->getOptionUuidById('cat', 31)}', '{$this->getOptionUuidById('artemis', 3)}'),
            ('{$this->getOptionUuidById('cat', 32)}', '{$this->getOptionUuidById('artemis', 6)}'),
            ('{$this->getOptionUuidById('cat', 33)}', '{$this->getOptionUuidById('artemis', 5)}'),
            ('{$this->getOptionUuidById('cat', 34)}', '{$this->getOptionUuidById('youtube', 29)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 34)}', '{$this->getOptionUuidById('jw', 17)}'),    
            ('{$this->getOptionUuidById('cat', 34)}', '{$this->getOptionUuidById('artemis', 1)}'),
            ('{$this->getOptionUuidById('cat', 35)}', '{$this->getOptionUuidById('youtube', 27)}'),                                                                                                                     
            ('{$this->getOptionUuidById('cat', 35)}', '{$this->getOptionUuidById('jw', 18)}'),    
            ('{$this->getOptionUuidById('cat', 35)}', '{$this->getOptionUuidById('artemis', 3)}');
        ");
    }

    private function getOptionUuidById(string $service, int $id): string
    {
        return $this->optionIds[$service][$id] ??= (string) (new UuidFactory())->create();
    }

    public function down(Schema $schema): void
    {
    }
}
