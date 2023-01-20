<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Chunk\ChunkFacade;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFacade;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageStatusFacade;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\FileSystem\NameGenerator\NameGenerator;
use AnzuSystems\CoreDamBundle\Helper\FileHelper;
use AnzuSystems\CoreDamBundle\Model\Dto\Asset\AssetAdmFinishDto;
use AnzuSystems\CoreDamBundle\Model\Dto\Chunk\ChunkAdmCreateDto;
use AnzuSystems\CoreDamBundle\Model\Dto\Image\ImageAdmCreateDto;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

#[AsCommand(
    name: 'anzu:asset:upload',
    description: 'Allow to upload assets.'
)]
final class UploadAssetCommand extends Command
{
    use OutputUtilTrait;

    private const LICENCE_ID_ARG = 'licenceId';
    private const FILE_PATH_ARG = 'filePath';

    public function __construct(
        private readonly AssetLicenceRepository $assetLicenceRepository,
        private readonly ChunkFacade $chunkFacade,
        private readonly NameGenerator $nameGenerator,
        private readonly ImageFacade $imageFacade,
        private readonly ImageStatusFacade $statusFacade,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->addArgument(
                name: self::LICENCE_ID_ARG,
                mode: InputArgument::REQUIRED,
            )
            ->addArgument(
                name: self::FILE_PATH_ARG,
                mode: InputArgument::REQUIRED,
            );
    }


    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $licenceId = (int)$input->getArgument(self::LICENCE_ID_ARG);
        $licence = $this->assetLicenceRepository->find($licenceId);

        if (false === ($licence instanceof AssetLicence)) {
            $this->outputUtil->error('Licence not found');

            return Command::FAILURE;
        }

        $filePath = $this->nameGenerator->getPath((string)$input->getArgument(self::FILE_PATH_ARG));

        try {
            $file = new UploadedFile($filePath->getFullPath(), $filePath->getFileName());
        } catch (Throwable) {
            $this->outputUtil->error('File not found');

            return Command::FAILURE;
        }

        // todo works only for images.
        $this->uploadAsset($file, $licence);

        return Command::SUCCESS;
    }

    /**
     * @throws SerializerException
     * @throws ValidationException
     */
    private function uploadAsset(UploadedFile $file, AssetLicence $licence): void
    {
        $checksum = FileHelper::checksumFromPath((string) $file->getRealPath());

        $assetFile = $this->imageFacade->createAssetFile(
            createDto: (new ImageAdmCreateDto())
                ->setMimeType((string)$file->getMimeType())
                ->setSize((int)$file->getSize())
                ->setChecksum($checksum),
            assetLicence: $licence
        );

        $this->outputUtil->writeln(
            sprintf(
                'Created Asset (%s) and file (%s)',
                $assetFile->getAsset()->getId(),
                $assetFile->getId()
            )
        );

        $chunk = $this->chunkFacade->create(
            createDto: (new ChunkAdmCreateDto)
                ->setOffset(0)
                ->setSize($file->getSize())
                ->setFile($file),
            assetFile: $assetFile
        );
        $this->outputUtil->writeln(sprintf('Created Chunk id (%s)', $chunk->getId()));

        $this->statusFacade->finishUpload(
            assetFinishDto: (new AssetAdmFinishDto())
                ->setChecksum($checksum),
            assetFile: $assetFile
        );
        $this->outputUtil->writeln('Upload finished');

        $table = new Table($this->outputUtil->getOutput());
        $table
            ->setHeaders(['Width', 'Height', 'ReqSize', 'Path'])
        ;
        foreach ($assetFile->getResizes() as $resize) {
            $table->addRow([
                $resize->getWidth(),
                $resize->getHeight(),
                $resize->getRequestedSize(),
                $resize->getFilePath(),
            ]);
        }
        $table->render();
    }
}
