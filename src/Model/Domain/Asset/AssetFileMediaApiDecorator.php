<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\App;
use App\Exception\ValidationException;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class AssetFileMediaApiDecorator
{
    #[Serialize]
    #[Assert\Range(minMessage: ValidationException::ERROR_FIELD_RANGE_MIN, min: 1)]
    protected int $mediaApiId = 0;

    #[Serialize]
    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    protected string $fullPath = '';

    #[Serialize]
    protected string $author = '';

    #[Serialize]
    protected string $keywords = '';

    #[Serialize]
    protected string $description = '';

    #[Serialize]
    protected DateTimeImmutable $updatedAt;

    #[Serialize]
    #[Assert\Range(minMessage: ValidationException::ERROR_FIELD_RANGE_MIN, min: 1)]
    protected int $idCentralUserUpdated = 0;

    #[Serialize]
    protected int $focusX = 0;

    #[Serialize]
    protected int $focusY = 0;

    #[Serialize]
    protected bool $public = true;

    #[Serialize]
    protected bool $singleUse = false;

    public function __construct()
    {
        $this->setUpdatedAt(App::getMinDate());
    }

    public function getMediaApiId(): int
    {
        return $this->mediaApiId;
    }

    public function setMediaApiId(int $mediaApiId): self
    {
        $this->mediaApiId = $mediaApiId;

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

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): self
    {
        $this->keywords = $keywords;

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

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getIdCentralUserUpdated(): int
    {
        return $this->idCentralUserUpdated;
    }

    public function setIdCentralUserUpdated(int $idCentralUserUpdated): self
    {
        $this->idCentralUserUpdated = $idCentralUserUpdated;

        return $this;
    }

    public function getFocusX(): int
    {
        return $this->focusX;
    }

    public function setFocusX(int $focusX): self
    {
        $this->focusX = $focusX;

        return $this;
    }

    public function getFocusY(): int
    {
        return $this->focusY;
    }

    public function setFocusY(int $focusY): self
    {
        $this->focusY = $focusY;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    public function setFullPath(string $fullPath): self
    {
        $this->fullPath = $fullPath;

        return $this;
    }

    public function isSingleUse(): bool
    {
        return $this->singleUse;
    }

    public function setSingleUse(bool $singleUse): self
    {
        $this->singleUse = $singleUse;

        return $this;
    }
}
