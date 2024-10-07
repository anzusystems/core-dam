<?php

declare(strict_types=1);

namespace App\Command;

use App\MediaApiMigrations\MediaApiFileCopy;
use App\Model\MediaApiMigrateConfig;
use Exception;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:media-api:copy',
    description: 'Migrate MediaApi'
)]
final class MediaApiCopyCommand extends Command
{
    public function __construct(
        private readonly MediaApiFileCopy $mediaApiFileCopy,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument(
            name: MediaApiMigrateConfig::FROM_ID_ARG,
            mode: InputArgument::OPTIONAL,
        );

        $this->addArgument(
            name: MediaApiMigrateConfig::TO_ID_ARG,
            mode: InputArgument::OPTIONAL,
        );
        $this->addOption(
            name: MediaApiMigrateConfig::DROP_MIGRATION_TABLE_OPTION,
            mode: InputOption::VALUE_NONE,
        );
        $this->addOption(
            name: MediaApiMigrateConfig::LIMIT_OPT,
            mode: InputOption::VALUE_OPTIONAL,
        );
        $this->addOption(
            name: MediaApiMigrateConfig::STAGES_OPT,
            mode: InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            default: MediaApiMigrateConfig::DEFAULT_STAGES
        );
        $this->addOption(
            name: MediaApiMigrateConfig::MEDIA_API_SOURCE_STORAGE_OPT,
            mode: InputOption::VALUE_REQUIRED,
            default: MediaApiMigrateConfig::REMOTE_STORAGE,
        );
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = MediaApiMigrateConfig::createFromInput($input);

        $this->mediaApiFileCopy->migrate($config);
        $output->writeln('');

        return Command::SUCCESS;
    }
}
