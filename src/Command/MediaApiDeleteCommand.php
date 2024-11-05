<?php

declare(strict_types=1);

namespace App\Command;

use App\MediaApiMigrations\DeleteDamFilesFromByMediaApi;
use Exception;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(
    name: 'anzu:media-api:force-delete',
    description: 'Prepare images to delete'
)]
final class MediaApiDeleteCommand extends Command
{
    public function __construct(
        private readonly DeleteDamFilesFromByMediaApi $apiImageDelete,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action? [y/n] ', false);

        if (false === $helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $this->apiImageDelete->migrate();
        $output->writeln('');

        return Command::SUCCESS;
    }
}
