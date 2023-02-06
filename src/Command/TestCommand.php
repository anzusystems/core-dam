<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Domain\CustomDistribution\CustomDistributionFacade;
use AnzuSystems\CoreDamBundle\Domain\Distribution\DistributionFacade;
use AnzuSystems\CoreDamBundle\Domain\Podcast\RssImportManager;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\CoreDamBundle\Repository\PodcastRepository;
use App\Domain\ArtemisAudioDistribution\ArtemisAudioDistributionAutomat;
use App\HttpClient\ArtemisClient;
use App\Model\Dto\Artemis\ArtemisAudioMediaDto;
use App\Model\Dto\Artemis\ArtemisImageDto;
use App\Model\Dto\Artemis\ArtemisMediaAuthorDto;
use App\Model\Dto\Artemis\ArtemisMediaChannel;
use App\Model\Dto\Artemis\ArtemisMediaDto;
use App\Model\Dto\Artemis\ArtemisMediaRubricDto;
use App\Model\Dto\Artemis\ArtemisMediaTagDto;
use App\Model\Enum\ArtemisMediaType;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
        private readonly HttpClientInterface $httpClient,
        private readonly PodcastRepository $podcastRepository,
        private readonly RssImportManager $importManager,
        private readonly ArtemisAudioDistributionAutomat $automat,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        # Audio Minimal
        $mediaDto = new ArtemisAudioMediaDto();
        $mediaDto
            ->setTitle('Test media')
            ->setMediaChannel((new ArtemisMediaChannel())->setAnzuId('8fe7196e-1480-41b6-b0b3-1c73c79f3452'))
            ->setRubric((new ArtemisMediaRubricDto())->setId(6978));
        $this->artemisRubricClient->createMedia($mediaDto);

        # Audio Maximal
        $mediaDto = new ArtemisAudioMediaDto();
        $mediaDto
            ->setImage(
                (
                new ArtemisImageDto())
                    ->setTitle('Title')
                    ->setUrl('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRspepjfyrVKhFNfHSjgVfgX_UP-w2vBXzqqg&usqp=CAU')
            )
            ->setTitle('Test media')
            ->setDescription('rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vrosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vPopis vtaka rosomaka Popis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomakaPopis vtaka rosomaka')
            ->setMediaChannel((new ArtemisMediaChannel())->setAnzuId('8fe7196e-1480-41b6-b0b3-1c73c79f3452'))
            ->setRubric((new ArtemisMediaRubricDto())->setId(6978))
            ->setAuthors([(new ArtemisMediaAuthorDto())->setFullName('Ondrej Podstupka')])
            ->setTags([(new ArtemisMediaTagDto())->setTitle('novy tagissssss')])
            ->setDirectSourceUrl('https://anchor.fm/s/40c6e0cc/podcast/play/63101018/https%3A%2F%2Fd3ctxlq1ktw2nl.cloudfront.net%2Fstaging%2F2023-0-5%2F76665c8c-7765-6587-57ec-42627834a9c2.mp3')
            ->setPremiumSourceUrl('https:\/\/dts.podtrac.com\/redirect.mp3\/chtbl.com\/track\/8DB4DB\/pdst.fm\/e\/nyt.simplecastaudio.com\/bbbcc290-ed3b-44a2-8e5d-5513e38cfe20\/episodes\/8ad3d11e-36e6-4144-9026-11dc89d1fa9a\/audio\/128\/default.mp3?awCollectionId=bbbcc290-ed3b-44a2-8e5d-5513e38cfe20\u0026awEpisodeId=8ad3d11e-36e6-4144-9026-11dc89d1fa9a')
            ->setBonus(true)
            ->setDuration(123)
            ->setPremiumDirectSourceDuration(1235)
            ->setExternalId('1234')
            ->setCreateArticle(true)
            ->setPublishedAt((new \DateTimeImmutable('+ 1 day')))
        ;
        $this->artemisRubricClient->createMedia($mediaDto);

        return Command::SUCCESS;
    }
}
