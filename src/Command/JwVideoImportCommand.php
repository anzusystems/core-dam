<?php

declare(strict_types=1);

namespace App\Command;

use App\JwVideoMigration\JwMigrator;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:jw:migrate',
    description: 'Migrate MediaApi'
)]
final class JwVideoImportCommand extends Command
{
    public function __construct(
        private JwMigrator $jwMigrator,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->jwMigrator->buildTable();
        $this->jwMigrator->migrate();

        return Command::SUCCESS;
    }
}
