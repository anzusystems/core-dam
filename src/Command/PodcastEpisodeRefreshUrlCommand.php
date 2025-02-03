<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CommonBundle\Csv\CsvHelper;
use AnzuSystems\Contracts\AnzuApp;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Domain\EntityIterator\EntityIterator;
use AnzuSystems\CoreDamBundle\Domain\EntityIterator\Model\EntityIteratorConfig;
use AnzuSystems\CoreDamBundle\Domain\EntityIterator\Visitor\EntityIteratorOnBatchFlushVisitor;
use AnzuSystems\CoreDamBundle\Domain\Podcast\PodcastImportIterator;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Model\Dto\Podcast\PodcastImportIteratorDto;
use AnzuSystems\CoreDamBundle\Model\ValueObject\PodcastSynchronizerPointer;
use AnzuSystems\CoreDamBundle\Repository\DistributionRepository;
use AnzuSystems\CoreDamBundle\Repository\PodcastEpisodeRepository;
use App\Configuration\ConfigurationProvider;
use App\Model\Configuration\ArtemisAudioDistributionConfiguration;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:podcast:episode:refresh-url',
    description: 'Refresh podcast episode url'
)]
final class PodcastEpisodeRefreshUrlCommand extends Command
{
    private const string PODCAST_EPISODE_LINKS_CSV = 'podcast_episode_links.csv';
    private const string ARTEMIS_PODCAST_CMS = 'artemis_podcast_cms';
    private const string PERSIST = 'persist';

    private const array CSV_FIELDS = [
        'podcast_title',
        'podcast_id',
        'episode_title',
        'episode_id',
        'audio_id',
        'artemis_distribution_id',
        'rss_url',
        'new_url',
    ];

    public function __construct(
        private readonly EntityIterator $entityIterator,
        private readonly PodcastImportIterator $importIterator,
        private readonly PodcastEpisodeRepository $podcastEpisodeRepository,
        private readonly ConfigurationProvider $configurationProvider,
        private readonly DistributionRepository $distributionRepository,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(
            name: self::PERSIST,
            mode: InputOption::VALUE_NONE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $persist = (bool) $input->getOption(self::PERSIST);
        $configuration = $this->configurationProvider->getAudioDistribution();
        $filePath = AnzuApp::getDataDir() . '/' . self::PODCAST_EPISODE_LINKS_CSV;
        CsvHelper::writeCsv($filePath);
        $csv = CsvHelper::appendCsv($filePath);
        $csv->fputcsv(self::CSV_FIELDS);

        $config = (new EntityIteratorConfig(batch: 100, useOnBatchVisitor: EntityIteratorOnBatchFlushVisitor::class));
        $from = App::getMinDate();

        /** @var Podcast $entity */
        foreach ($this->entityIterator->iterateEntities(Podcast::class, $config) as $entity) {
            $generator = $this->importIterator->iteratePodcast(
                (new PodcastSynchronizerPointer($entity->getId(), $from)),
                $entity,
                $from
            );

            foreach ($generator as $item) {
                $this->syncUrl($item, $csv, $configuration, $persist);
            }
        }

        $output->writeln('Done');

        return Command::SUCCESS;
    }

    private function syncUrl(
        PodcastImportIteratorDto $item,
        SplFileObject $csv,
        ArtemisAudioDistributionConfiguration $configuration,
        bool $persist,
    ): void {
        $rssUrl = $item->getItem()->getEnclosure()->getUrl();
        $podcastEpisode = $this->podcastEpisodeRepository->findOneTitleAndPodcast(
            $item->getItem()->getTitle(),
            $item->getPodcast()
        );

        if (null === $podcastEpisode) {
            return;
        }

        if (App::ZERO === strcmp($rssUrl, $podcastEpisode->getAttributes()->getRssUrl())) {
            return;
        }

        $freeAudio = $podcastEpisode->getAsset()?->getSlots()->findFirst(
            fn (int $index, AssetSlot $slot): bool => $slot->getName() === $configuration->getAudioFreeSlotName()
        );

        $artemisDistribution = $freeAudio
            ? $this->distributionRepository->findByAssetFileAndDistributionService(
                $freeAudio->getId(),
                self::ARTEMIS_PODCAST_CMS
            )
            : null
        ;

        $csv->fputcsv([
            $item->getPodcast()->getTexts()->getTitle(),
            $item->getPodcast()->getId(),
            $podcastEpisode->getTexts()->getTitle(),
            $podcastEpisode->getId(),
            $freeAudio?->getAudio()?->getId(),
            $artemisDistribution?->getExtId(),
            $rssUrl,
            $item->getItem()->getEnclosure()->getUrl(),
        ]);

        if ($persist) {
            $podcastEpisode->getAttributes()->setRssUrl($rssUrl);
        }
    }
}
