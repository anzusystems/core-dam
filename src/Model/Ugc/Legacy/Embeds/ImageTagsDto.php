<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ImageTagsDto
{
    #[Serialize]
    private string $headline = '';

    #[Serialize]
    private string $title = '';

    #[Serialize]
    private string $description = '';

    #[Serialize]
    private string $creator = '';

    #[Serialize]
    private string $event = '';

    #[Serialize]
    private string $personShown = '';

    #[Serialize]
    private string $keywords = '';

    #[Serialize]
    private string $author = '';

    #[Serialize]
    private string $orientation = '';

    #[Serialize]
    private string $colorSpace = '';

    public static function getInstance(ImageFile $imageFile): self
    {
        $exifData = $imageFile->getMetadata()->getExifData();

        return (new self())
            ->setHeadline($exifData['Headline'] ?? '')
            ->setTitle($exifData['Title'] ?? '')
            ->setDescription($exifData['Description'] ?? '')
            ->setCreator($exifData['Creator'] ?? '')
            ->setEvent($exifData['Event'] ?? '')
            ->setPersonShown($exifData['PersonInImage'] ?? '')
            ->setKeywords($exifData['Keywords'] ?? '')
            ->setAuthor($exifData['Author'] ?? '')
            ->setOrientation($exifData['Orientation'] ?? '')
            ->setColorSpace($exifData['Color Space'] ?? '')
        ;
    }

    public function getHeadline(): string
    {
        return $this->headline;
    }

    public function setHeadline(string $headline): self
    {
        $this->headline = $headline;

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreator(): string
    {
        return $this->creator;
    }

    public function setCreator(string $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getPersonShown(): string
    {
        return $this->personShown;
    }

    public function setPersonShown(string $personShown): self
    {
        $this->personShown = $personShown;

        return $this;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function setOrientation(string $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getColorSpace(): string
    {
        return $this->colorSpace;
    }

    public function setColorSpace(string $colorSpace): self
    {
        $this->colorSpace = $colorSpace;

        return $this;
    }
}
