<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Entity\VideoShowEpisode;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Model\Domain\Distribution\DistributionPubDecoratorInterface;
use App\Model\Domain\VideoShowEpisode\AssetVideoShowEpisodePubDecorator;

final class VideoAssetPubDecorator extends AbstractAssetPubDecorator
{
    private ?VideoShowEpisode $videoShowEpisode = null;
    private VideoFile $videoFile;

    /**
     * @var array<int, DistributionPubDecoratorInterface>
     */
    private array $distributions = [];

    public static function getInstance(
        Asset $asset,
        VideoFile $videoFile,
        array $distributions,
        string $metadataTitleField,
        ?VideoShowEpisode $videoShowEpisode = null,
    ): static {
        return parent::getBaseInstance($asset, $metadataTitleField)
            ->setDistributions($distributions)
            ->setVideoFile($videoFile)
            ->setVideoShowEpisode($videoShowEpisode)
        ;
    }

    public function getVideoShowEpisode(): ?VideoShowEpisode
    {
        return $this->videoShowEpisode;
    }

    public function setVideoShowEpisode(?VideoShowEpisode $videoShowEpisode): self
    {
        $this->videoShowEpisode = $videoShowEpisode;
        return $this;
    }

    public function getVideoFile(): VideoFile
    {
        return $this->videoFile;
    }

    public function setVideoFile(VideoFile $videoFile): self
    {
        $this->videoFile = $videoFile;
        return $this;
    }

    #[Serialize]
    public function getThumbnail(): VideoAssetThumbnailPubDecorator
    {
        return VideoAssetThumbnailPubDecorator::getInstance($this->getVideoFile()->getImagePreview()?->getImageFile());
    }

    #[Serialize]
    public function getDistributions(): array
    {
        return $this->distributions;
    }

    public function setDistributions(array $distributions): self
    {
        $this->distributions = $distributions;
        return $this;
    }

    #[Serialize]
    public function getVideoShow(): ?AssetVideoShowEpisodePubDecorator
    {
        return null === $this->videoShowEpisode
            ? null
            : AssetVideoShowEpisodePubDecorator::getInstance($this->videoShowEpisode)
        ;
    }

    #[Serialize]
    public function getDuration(): int
    {
        return $this->getVideoFile()->getAttributes()->getDuration();
    }
}
