<?php

declare(strict_types=1);

namespace App\Model\Csv;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class JwCsvFile
{
    #[Serialize(serializedName: 'id_media')]
    private int $id = 0;

    #[Serialize(serializedName: 'id_media_service')]
    private int $mediaServiceId = 0;

    #[Serialize(serializedName: 'title_key')]
    private string $mediaServiceTitle = '';

    #[Serialize(serializedName: 'title')]
    private string $title = '';

    #[Serialize(serializedName: 'source_id')]
    private string $sourceId = '';

    #[Serialize(serializedName: 'source_id_alt')]
    private string $sourceIdYt = '';

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

    public function getMediaServiceTitle(): string
    {
        return $this->mediaServiceTitle;
    }

    public function setMediaServiceTitle(string $mediaServiceTitle): self
    {
        $this->mediaServiceTitle = $mediaServiceTitle;
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

    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    public function setSourceId(string $sourceId): self
    {
        $this->sourceId = $sourceId;
        return $this;
    }

    public function getSourceIdYt(): string
    {
        return $this->sourceIdYt;
    }

    public function setSourceIdYt(string $sourceIdYt): self
    {
        $this->sourceIdYt = $sourceIdYt;
        return $this;
    }
}
