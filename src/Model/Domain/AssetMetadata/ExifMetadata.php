<?php

declare(strict_types=1);

namespace App\Model\Domain\AssetMetadata;

use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ExifMetadata
{
    private const string AUTHOR_SEPARATOR = ',';

    #[Serialize(serializedName: 'Description')]
    private string $description = '';

    #[Serialize(serializedName: 'Title')]
    private string $title = '';

    #[Serialize(serializedName: 'Subject')]
    private string $subject = '';

    #[Serialize(serializedName: 'Headline')]
    private string $headline = '';

    #[Serialize(serializedName: 'Creator')]
    private string $creator = '';

    #[Serialize(serializedName: 'Keywords')]
    private string $keywords = '';

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
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

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
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

    public function getCreator(): string
    {
        return $this->creator;
    }

    public function setCreator(string $creator): self
    {
        $this->creator = $creator;
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

    public function getAssetTitle(): string
    {
        if (false === empty($this->getTitle())) {
            return $this->getTitle();
        }
        if (false === empty($this->getSubject())) {
            return $this->getSubject();
        }
        if (false === empty($this->getHeadline())) {
            return $this->getHeadline();
        }
        return '';
    }

    public function getAssetDescription(): string
    {
        return $this->getDescription();
    }

    /**
     * @return string[]
     */
    public function getAssetAuthors(): array
    {
        return array_filter(
            array_map(
                fn (string $value): string => StringHelper::parseString($value, 255),
                explode(self::AUTHOR_SEPARATOR, $this->getCreator())
            ),
            fn (string $value): bool => false === empty($value)
        );
    }
}
