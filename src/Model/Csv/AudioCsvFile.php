<?php

declare(strict_types=1);

namespace App\Model\Csv;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class AudioCsvFile
{
    #[Serialize(serializedName: 'id_media')]
    private int $id = 0;

    #[Serialize(serializedName: 'id_media_service')]
    private int $mediaServiceId = 0;

    #[Serialize(serializedName: 'title')]
    private string $title = '';

    #[Serialize(serializedName: 'direct_source_url')]
    private string $directSourceUrl = '';

    #[Serialize(serializedName: 'title_channel')]
    private string $titleChannel = '';

    #[Serialize(serializedName: 'rss_feed')]
    private string $rssFeed = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getMediaServiceId(): int
    {
        return $this->mediaServiceId;
    }

    public function setMediaServiceId(int $mediaServiceId): self
    {
        $this->mediaServiceId = $mediaServiceId;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
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

    public function getTitleChannel(): string
    {
        return $this->titleChannel;
    }

    public function setTitleChannel(string $titleChannel): self
    {
        $this->titleChannel = $titleChannel;
        return $this;
    }

    public function getRssFeed(): string
    {
        return $this->rssFeed;
    }

    public function setRssFeed(string $rssFeed): self
    {
        $this->rssFeed = $rssFeed;
        return $this;
    }
}
