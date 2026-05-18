<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Domain\Tts\Command\CancelJob;
use AnzuSystems\CoreDamBundle\Repository\TtsAssetRepository;
use App\App;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Cancel TTS regen jobs that are stuck in 'superseding' status longer than the given TTL.
 *
 * Usage:
 *   bin/console app:tts:cleanup-stuck-regen
 *   bin/console app:tts:cleanup-stuck-regen --older-than=30m
 *   bin/console app:tts:cleanup-stuck-regen --older-than=2h --dry-run
 */
#[AsCommand(
    name: 'app:tts:cleanup-stuck-regen',
    description: 'Cancel TTS regen jobs stuck in superseding status for too long',
)]
final class TtsCleanupStuckRegenCommand extends Command
{
    private const string OPT_OLDER_THAN = 'older-than';
    private const string OPT_DRY_RUN = 'dry-run';

    public function __construct(
        private readonly TtsAssetRepository $ttsAssetRepository,
        private readonly CancelJob $cancelJob,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                self::OPT_OLDER_THAN,
                null,
                InputOption::VALUE_REQUIRED,
                'Duration threshold for "stuck" regen — e.g. 1h, 30m, 2h30m',
                '1h',
            )
            ->addOption(
                self::OPT_DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Print matched assets without cancelling',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        App::throwOnReadOnlyMode();

        $olderThanStr = (string) $input->getOption(self::OPT_OLDER_THAN);
        $dryRun = (bool) $input->getOption(self::OPT_DRY_RUN);

        $interval = $this->parseDuration($olderThanStr);
        if (null === $interval) {
            $output->writeln(sprintf('<error>Cannot parse duration "%s". Use e.g. 1h, 30m, 2h30m.</error>', $olderThanStr));

            return Command::FAILURE;
        }

        $threshold = (new DateTimeImmutable())->sub($interval);
        $cancelledCount = 0;

        $ttsAssets = $this->ttsAssetRepository->findStuckSuperseding($threshold);
        foreach ($ttsAssets as $ttsAsset) {
            $assetId = (string) $ttsAsset->getAsset()->getId();
            $regenJobId = $ttsAsset->getRegenJobId();

            if (null === $regenJobId) {
                $output->writeln(sprintf('Asset %s is stuck in superseding without regenJobId — skipping.', $assetId));

                continue;
            }

            if ($dryRun) {
                $output->writeln(sprintf('[dry-run] Would cancel job %s for asset %s (modified before %s)', $regenJobId, $assetId, $threshold->format('Y-m-d H:i:s')));
                ++$cancelledCount;

                continue;
            }

            $this->cancelJob->execute(
                $regenJobId,
                sprintf('auto-cleanup: regen exceeded TTL of %s', $olderThanStr),
                null,
            );
            ++$cancelledCount;
            $output->writeln(sprintf('Cancelled job %s for asset %s', $regenJobId, $assetId));
        }

        $output->writeln(sprintf('%s: %d asset(s) processed%s.', $dryRun ? 'DRY RUN' : 'DONE', $cancelledCount, $dryRun ? ' (no changes made)' : App::EMPTY_STRING));

        return Command::SUCCESS;
    }

    /**
     * Parse a duration string like '1h', '30m', '2h30m' into a DateInterval.
     * Returns null if the format is unrecognised.
     */
    private function parseDuration(string $duration): ?DateInterval
    {
        if (preg_match('/^(?:(\d+)h)?(?:(\d+)m)?$/', $duration, $matches) && ('' !== $duration)) {
            $hours = (int) ($matches[1] ?? 0);
            $minutes = (int) ($matches[2] ?? 0);

            if (App::ZERO === $hours && App::ZERO === $minutes) {
                return null;
            }

            return new DateInterval(sprintf('PT%dH%dM', $hours, $minutes));
        }

        return null;
    }
}
