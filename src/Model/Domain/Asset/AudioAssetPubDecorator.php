<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Model\Domain\Podcast\AssetPodcastPubDecorator;

final class AudioAssetPubDecorator extends AbstractAssetPubDecorator
{
    private ?PodcastEpisode $podcastEpisode = null;
    private AudioFile $audioFile;
    private array $media = [];
    private PublicExport $publicExport;

    public static function getInstance(
        Asset $asset,
        AudioFile $audioFile,
        array $media,
        string $metadataTitleField,
        PublicExport $publicExport,
        ?PodcastEpisode $podcastEpisode = null,
    ): static {
        return parent::getBaseInstance($asset, $metadataTitleField)
            ->setMedia($media)
            ->setAudioFile($audioFile)
            ->setPodcastEpisode($podcastEpisode)
            ->setPublicExport($publicExport)
        ;
    }

    public function getPublicExport(): PublicExport
    {
        return $this->publicExport;
    }

    public function setPublicExport(PublicExport $publicExport): self
    {
        $this->publicExport = $publicExport;
        return $this;
    }

    public function getAudioFile(): AudioFile
    {
        return $this->audioFile;
    }

    public function setAudioFile(AudioFile $audioFile): self
    {
        $this->audioFile = $audioFile;
        return $this;
    }

    public function getPodcastEpisode(): ?PodcastEpisode
    {
        return $this->podcastEpisode;
    }

    public function setPodcastEpisode(?PodcastEpisode $podcastEpisode): self
    {
        $this->podcastEpisode = $podcastEpisode;
        return $this;
    }

    #[Serialize]
    public function getMedia(): array
    {
        return $this->media;
    }

    public function setMedia(array $media): self
    {
        $this->media = $media;
        return $this;
    }

    #[Serialize]
    public function getPodcast(): ?AssetPodcastPubDecorator
    {
        return null === $this->podcastEpisode
            ? null
            : AssetPodcastPubDecorator::getInstance($this->podcastEpisode, $this->publicExport)
        ;
    }

    #[Serialize]
    public function getThumbnail(): AudioAssetThumbnailPubDecorator
    {
        return AudioAssetThumbnailPubDecorator::getInstance($this->getAudioFile());
    }
}
