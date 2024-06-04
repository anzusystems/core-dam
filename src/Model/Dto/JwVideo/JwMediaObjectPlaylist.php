<?php

declare(strict_types=1);

namespace App\Model\Dto\JwVideo;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class JwMediaObjectPlaylist
{
    #[Serialize(serializedName: 'pubdate')]
    private int $pubDate = 0;

    #[Serialize]
    private int $duration = 0;

    #[Serialize(type: JwMediaObjectPlaylistSource::class)]
    private Collection $sources;

    public function __construct()
    {
        $this->sources = new ArrayCollection();
    }

    public function getPubDate(): int
    {
        return $this->pubDate;
    }

    public function setPubDate(int $pubDate): self
    {
        $this->pubDate = $pubDate;
        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return Collection<int, JwMediaObjectPlaylistSource>
     */
    public function getSources(): Collection
    {
        return $this->sources;
    }

    public function setSources(Collection $sources): self
    {
        $this->sources = $sources;
        return $this;
    }
}
