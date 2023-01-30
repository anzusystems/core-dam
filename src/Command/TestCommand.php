<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Domain\CustomDistribution\CustomDistributionFacade;
use AnzuSystems\CoreDamBundle\Domain\Distribution\DistributionFacade;
use AnzuSystems\CoreDamBundle\Entity\CustomDistribution;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use App\DamMigrations\AdmUserMigrations;
use App\DamMigrations\AssetImageMigrations;
use App\DamMigrations\AudioCategoryMigrations;
use App\DamMigrations\LegacyDamPodcastMigrations;
use App\DamMigrations\UgcLicenceMigrations;
use App\DamMigrations\UgcUserMigrations;
use App\HttpClient\ArtemisClient;
use App\Model\Dto\Artemis\ArtemisAudioMediaDto;
use App\Model\Dto\Artemis\ArtemisImageDto;
use App\Model\Dto\Artemis\ArtemisMediaAuthorDto;
use App\Model\Dto\Artemis\ArtemisMediaChannel;
use App\Model\Dto\Artemis\ArtemisMediaDto;
use App\Model\Dto\Artemis\ArtemisMediaRubricDto;
use App\Model\Dto\Artemis\ArtemisMediaTagDto;
use App\Model\Dto\Artemis\ArtemisRubricDto;
use App\Model\MigrateConfig;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:test',
    description: 'Create mandatory users.'
)]
final class TestCommand extends Command
{
    public function __construct(
        private readonly ArtemisClient $artemisRubricClient,
        private readonly AudioFileRepository $audioFileRepository,
        private readonly CustomDistributionFacade $customDistributionFacade,
        private readonly DistributionFacade $distributionFacade,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $audio = $this->audioFileRepository->findAll()[0];

        $res = $this->distributionFacade->distribute(
            $audio,
            (new CustomDistribution())
                ->setDistributionService('artemis_podcast_cms')
                ->setCustomData([
                    'title' => '783: Kids These Days',
                    'description' => 'Custom audio description',
                    'keywords' => [
                        'News',
                        'Podcast',
                        'Politics',
                    ],
                    'authors' => [
                        'Aarne Ormonde',
                        'Larry Queen',
                        'Malka Raisa',
                    ],
                    'createArticle' => true,
                ])
        );

        //        $res = $this->artemisRubricClient->getRubricsBySectionId(119);
        //        $mediaDto = new ArtemisMediaDto();
        //        $mediaDto
        //            ->setImage((
        //                new ArtemisImageDto)
        //                    ->setTitle('Title')
        //                    ->setUrl('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRspepjfyrVKhFNfHSjgVfgX_UP-w2vBXzqqg&usqp=CAU')
        //            )
        //            ->setTitle('Test media')
        //            ->setRubric(
        //                (new ArtemisMediaRubricDto())->setId(6978)
        //            )
        //            ->setAuthors([
        //                (new ArtemisMediaAuthorDto())
        //                    ->setFullName( 'Ondrej Podstupka')
        //            ])
        //            ->setTags([
        //                (new ArtemisMediaTagDto())
        //                    ->setTitle('novy tagissssss')
        //            ])
        //        ;

        //        $mediaDto = new ArtemisAudioMediaDto();
        //        $mediaDto
        //            ->setImage((
        //            new ArtemisImageDto)
        //                ->setTitle('Title')
        //                ->setUrl('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRspepjfyrVKhFNfHSjgVfgX_UP-w2vBXzqqg&usqp=CAU')
        //            )
        //            ->setTitle('Test media')
        //            ->setAnzuMediaId('123')
        //            ->setExternalId('123')
        //
        //            ->setDirectSourceUrl('https://anchor.fm/s/40c6e0cc/podcast/play/63101018/https%3A%2F%2Fd3ctxlq1ktw2nl.cloudfront.net%2Fstaging%2F2023-0-5%2F76665c8c-7765-6587-57ec-42627834a9c2.mp3')
        //            ->setPremiumDirectSourceUrl('')
        //            ->setCreateArticle(true)
        //            ->setMediaChannel(
        //                (new ArtemisMediaChannel())->setAnzuId('nehehehe')
        //            )
        //            ->setRubric(
        //                (new ArtemisMediaRubricDto())->setId(6978)
        //            )
        //            ->setAuthors([
        //                (new ArtemisMediaAuthorDto())
        //                    ->setFullName( 'Ondrej Podstupka')
        //            ])
        //            ->setTags([
        //                (new ArtemisMediaTagDto())
        //                    ->setTitle('novy tagissssss')
        //            ])
        //        ;
        //
        //        $resp = $this->artemisRubricClient->createMedia($mediaDto);
        //        dump($resp);

        return Command::SUCCESS;
    }
}
