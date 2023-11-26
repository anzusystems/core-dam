<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Model\Enum\ArtemisMediaType;
use DateTimeImmutable;

class ArtemisAudioMediaDto extends ArtemisMediaDto
{
    #[Serialize]
    protected string $anzuPodcastEpisodeId = '';
    #[Serialize]
    private ArtemisMediaChannel $mediaChannel;

    #[Serialize]
    private string $directSourceUrl = '';

    #[Serialize]
    private string $premiumSourceUrl = '';

    #[Serialize(type: 'Y-m-d\TH:i:sP')]
    private ?DateTimeImmutable $publishedAt = null;

    public function __construct()
    {
        $this->setType(ArtemisMediaType::Audio->toString());
        $this->setMediaChannel(new ArtemisMediaChannel());
        parent::__construct();
    }

    public function getDirectSourceUrl(): string
    {
        return $this->directSourceUrl;
    }

    public function setDirectSourceUrl(string $directSourceUrl): self
    {
        $this->directSourceUrl = $directSourceUrl;

        return $this;
    }

    public function getAnzuPodcastEpisodeId(): string
    {
        return $this->anzuPodcastEpisodeId;
    }

    public function setAnzuPodcastEpisodeId(string $anzuPodcastEpisodeId): self
    {
        $this->anzuPodcastEpisodeId = $anzuPodcastEpisodeId;

        return $this;
    }

    public function getPremiumSourceUrl(): string
    {
        return $this->premiumSourceUrl;
    }

    public function setPremiumSourceUrl(string $premiumSourceUrl): self
    {
        $this->premiumSourceUrl = $premiumSourceUrl;

        return $this;
    }

    public function getMediaChannel(): ArtemisMediaChannel
    {
        return $this->mediaChannel;
    }

    public function setMediaChannel(ArtemisMediaChannel $mediaChannel): self
    {
        $this->mediaChannel = $mediaChannel;

        return $this;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}
