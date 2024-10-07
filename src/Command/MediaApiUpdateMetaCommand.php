<?php

declare(strict_types=1);

namespace App\Command;

use App\Helper\ScalarTypesHelper;
use App\MediaApiMigrations\MediaApiMetadataUpdate;
use Exception;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:media-api:update-metadata',
    description: 'Update metadata from mediaapi'
)]
final class MediaApiUpdateMetaCommand extends Command
{
    public const string FROM_ID_ARG = 'fromId';
    public const string LIMIT_OPT = 'limit';
    public const string TO_ID_ARG = 'toId';

    public function __construct(
        private readonly MediaApiMetadataUpdate $metadataUpdate,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument(
            name: self::FROM_ID_ARG,
            mode: InputArgument::OPTIONAL,
        );

        $this->addArgument(
            name: self::TO_ID_ARG,
            mode: InputArgument::OPTIONAL,
        );
        $this->addOption(
            name: self::LIMIT_OPT,
            mode: InputOption::VALUE_OPTIONAL,
        );
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fromId = ScalarTypesHelper::getIntOrNull($input->getArgument(self::FROM_ID_ARG));
        $toId = ScalarTypesHelper::getIntOrNull($input->getArgument(self::TO_ID_ARG));

        $this->metadataUpdate->migrate(
            $fromId,
            $toId
        );
        $output->writeln('');

        return Command::SUCCESS;
    }
}
