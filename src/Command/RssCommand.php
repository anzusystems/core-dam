<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\PubSub\PubSubClient;
use phpseclib3\Common\Functions\Strings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:rss',
    description: 'Create app topics'
)]
final class RssCommand extends Command
{
    private const TOPIC_NAME_ARG = 'topic';

    public function __construct(
        public readonly AudioFileRepository $audioFileRepository,
    ) {
        parent::__construct();
    }

    public function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $audioFile = $this->audioFileRepository->findAll()[0];
        dump($audioFile);

        return Command::SUCCESS;
    }
}
