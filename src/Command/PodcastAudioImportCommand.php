<?php

declare(strict_types=1);

namespace App\Command;

use App\JwVideoMigration\AudioPodcastMigrator;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:audio:podcast-migrate',
    description: 'Migrate MediaApi'
)]
final class PodcastAudioImportCommand extends Command
{
    public function __construct(
        private AudioPodcastMigrator $audioPodcastMigrator,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->audioPodcastMigrator->migrate();

        return Command::SUCCESS;
    }
}
