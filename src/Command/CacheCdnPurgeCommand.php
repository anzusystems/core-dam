<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\Cache\ImageFileUrlCdnPurger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:cache:cdn-purge',
    description: 'Purge CDN cache for a given image ID.'
)]
final class CacheCdnPurgeCommand extends Command
{
    use OutputUtilTrait;

    private const string IMAGE_ID_ARG = 'image_id';
    private const string EXT_SYSTEM_SLUG_ARG = 'ext_system_slug';
    private const string ROI_OPT = 'roi';

    public function __construct(
        private readonly ImageFileUrlCdnPurger $imageFileUrlCdnPurger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: self::IMAGE_ID_ARG,
                mode: InputArgument::REQUIRED,
                description: 'Image ID to purge from CDN',
            )
            ->addArgument(
                name: self::EXT_SYSTEM_SLUG_ARG,
                mode: InputArgument::REQUIRED,
                description: 'External system slug',
            )
            ->addOption(
                name: self::ROI_OPT,
                mode: InputOption::VALUE_REQUIRED,
                description: 'Comma-separated ROI positions (e.g. 0,1,2)',
                default: '0',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $imageId = (string) $input->getArgument(self::IMAGE_ID_ARG);
        $extSystemSlug = (string) $input->getArgument(self::EXT_SYSTEM_SLUG_ARG);
        $roiPositions = array_map(
            static fn (string $position): int => (int) trim($position),
            explode(',', (string) $input->getOption(self::ROI_OPT))
        );

        $this->imageFileUrlCdnPurger->purge($imageId, $roiPositions, $extSystemSlug);
        $this->outputUtil->info(sprintf(
            'CDN purge queued for image ID: %s, ext system: %s, ROI positions: %s',
            $imageId,
            $extSystemSlug,
            implode(', ', $roiPositions)
        ));

        return Command::SUCCESS;
    }
}
