<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CommonBundle\Traits\LoggerAwareRequest;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\PodcastImport\RssImportManager;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\CoreDamBundle\Repository\PodcastRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:podcast:read-rss',
    description: 'Reads Podcast RSS'
)]
final class ReadPodcastRssCommand extends Command
{
    use LoggerAwareRequest;

    private const NODE_CHANNEL = 'channel';
    private const NODE_ITEM = 'item';

    public function __construct(
        private readonly RssImportManager $manager,
        private readonly PodcastRepository $podcastRepository,
        private readonly AudioFileRepository $audioFileRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Podcast $podcast */
        $podcast = $this->podcastRepository->findAll()[0];
        $this->manager->readPodcastRss($podcast);

        return Command::SUCCESS;
    }
}
