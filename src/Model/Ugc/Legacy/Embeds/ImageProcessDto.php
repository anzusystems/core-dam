<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ImageProcessDto
{
    #[Serialize]
    private string $processState = '';

    public static function getInstance(ImageFile $imageFile): self
    {
        $status = $imageFile->getAssetAttributes()->getStatus();

        return (new self())
            ->setProcessState(
                match ($status) {
                    AssetFileProcessStatus::Stored => 'uploading',
                    default => $imageFile->getAssetAttributes()->getStatus()->toString(),
                }
            )
        ;
    }

    public function getProcessState(): string
    {
        return $this->processState;
    }

    public function setProcessState(string $processState): self
    {
        $this->processState = $processState;

        return $this;
    }
}
