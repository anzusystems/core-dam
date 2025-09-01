<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetManager;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Repository\AbstractAssetFileRepository;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\CoreDamBundle\Repository\DocumentFileRepository;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use AnzuSystems\CoreDamBundle\Repository\VideoFileRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:asset:refresh-properties',
    description: 'Create mandatory users.'
)]
final class RefreshAssetFilePropertiesCommand extends Command
{
    use OutputUtilTrait;

    private const string ASSET_TYPE_ARG = 'asset_type';
    private const int BULK_COUNT = 50;

    public function __construct(
        private readonly AudioFileRepository $audioFileRepository,
        private readonly ImageFileRepository $imageFileRepository,
        private readonly DocumentFileRepository $documentFileRepository,
        private readonly VideoFileRepository $videoFileRepository,
        private readonly AssetManager $manager,
    ) {
        parent::__construct();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function updateExisting(AssetType $assetType): void
    {
        $repository = $this->getRepository($assetType);

        $progress = $this->outputUtil->createProgressBar();
        $progress->setFormat('debug');
        $progress->start();

        $assetFiles = $repository->findAllProcessed(self::BULK_COUNT);
        $lastId = null;
        while (false === $assetFiles->isEmpty()) {
            /** @var AssetFile $assetFile */
            foreach ($assetFiles as $assetFile) {
                $lastId = $assetFile->getId();
                $this->manager->updateExisting($assetFile->getAsset(), false);
                $progress->advance();
            }

            $this->flushAndClear();

            $assetFiles = $repository->findAllProcessed(self::BULK_COUNT, $lastId);
        }

        $progress->finish();
        $this->flushAndClear();
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: self::ASSET_TYPE_ARG,
            mode: InputArgument::REQUIRED,
            description: 'asset type',
        );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $assetType = AssetType::tryFrom((string) $input->getArgument(self::ASSET_TYPE_ARG));
        if (null === $assetType) {
            $this->outputUtil->error('Invalid asset type');

            return Command::FAILURE;
        }

        $this->updateExisting($assetType);

        return Command::SUCCESS;
    }

    private function flushAndClear(): void
    {
        $this->manager->flush();
        $this->manager->clear();
    }

    private function getRepository(AssetType $assetType): AbstractAssetFileRepository
    {
        return match ($assetType) {
            AssetType::Image => $this->imageFileRepository,
            AssetType::Video => $this->videoFileRepository,
            AssetType::Audio => $this->audioFileRepository,
            AssetType::Document => $this->documentFileRepository,
        };
    }
}
