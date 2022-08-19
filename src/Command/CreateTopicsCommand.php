<?php

declare(strict_types=1);

namespace App\Command;

use Anzu\CommonBundle\DataFixtures\FixturesLoader;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:topic:create',
    description: 'Create app topics'
)]
final class CreateTopicsCommand extends Command
{
    private const TOPIC_NAME_ARG = 'topic';

    public function __construct(
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->addArgument(
                name: self::TOPIC_NAME_ARG,
                mode: InputArgument::REQUIRED
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $topicName = $input->getArgument(self::TOPIC_NAME_ARG);
        $pubSubClient = new PubSubClient();

        try {
            $topic = $pubSubClient->topic($topicName)->info()['name'];

            $output->writeln(sprintf('Topic %s exists', $topic));
        } catch (NotFoundException)
        {
            $pubSubClient->createTopic($topicName);
            $output->writeln('Topic created');
        }

        return Command::SUCCESS;
    }
}
