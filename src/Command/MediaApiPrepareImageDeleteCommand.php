<?php

declare(strict_types=1);

namespace App\Command;

use App\MediaApiMigrations\MediaApiPrepareImageDelete;
use Exception;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:media-api:prepare-delete',
    description: 'Prepare images to delete'
)]
final class MediaApiPrepareImageDeleteCommand extends Command
{
    public function __construct(
        private readonly MediaApiPrepareImageDelete $apiImageDelete,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->apiImageDelete->migrate();
        $output->writeln('');

        return Command::SUCCESS;
    }
}
