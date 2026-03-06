<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use App\Cache\AssetFileCachePurger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:cache:proxy-purge',
    description: 'Purge cache proxy for a given image ID.'
)]
final class CacheProxyPurgeCommand extends Command
{
    use OutputUtilTrait;

    private const string IMAGE_ID_ARG = 'image_id';

    public function __construct(
        private readonly AssetFileCachePurger $assetFileCachePurger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: self::IMAGE_ID_ARG,
            mode: InputArgument::REQUIRED,
            description: 'Image ID to purge from cache proxy',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $imageId = (string) $input->getArgument(self::IMAGE_ID_ARG);

        $this->assetFileCachePurger->purge(AssetType::Image, $imageId);
        $this->outputUtil->info(sprintf('Cache proxy purge queued for image ID: %s', $imageId));

        return Command::SUCCESS;
    }
}
