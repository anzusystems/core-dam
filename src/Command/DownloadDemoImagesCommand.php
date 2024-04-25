<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\DataFixtures\Provider\UnsplashImageProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:image-fixtures:download',
    description: 'Download demo image data.'
)]
final class DownloadDemoImagesCommand extends Command
{
    private const ARG_COUNT = 'count';
    private const ARG_COUNT_DEFAULT = 20;

    public function __construct(
        private readonly UnsplashImageProvider $imageProvider,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument(
            name: self::ARG_COUNT,
            mode: InputArgument::OPTIONAL,
            default: self::ARG_COUNT_DEFAULT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // todo move to bundle, create config model, add flag download if empty folder
        $this->imageProvider->downloadImages(
            (int) $input->getArgument(self::ARG_COUNT),
            ['nature', 'people', 'city', 'politics', 'animals', 'girls', 'food'],
            [
                [1_024, 768],
                [768, 1_024],
                [1_280, 1_024],
                [720, 576],
                [576, 720],
                [1_280, 720],
                [1_920, 1_080],
                [1_080, 1_920],
                [1_080, 1_080],
                [576, 576],
            ]
        );

        return Command::SUCCESS;
    }
}
