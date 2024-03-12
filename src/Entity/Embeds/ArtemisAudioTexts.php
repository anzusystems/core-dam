<?php

declare(strict_types=1);

namespace App\Entity\Embeds;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class ArtemisAudioTexts
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    #[Assert\Length(max: 255, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[Serialize]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 256)]
    #[Assert\Length(max: 100, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[Serialize]
    private string $extRssId;

    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    #[Assert\Length(max: 5_000, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[ORM\Column(type: Types::STRING, length: 5_000)]
    #[Serialize]
    private string $description;

    #[Assert\Url(message: ValidationException::ERROR_FIELD_INVALID)]
    #[Assert\Length(max: 5_000, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[ORM\Column(type: Types::STRING, length: 2_048)]
    #[Serialize]
    private string $freeUrl;

    #[Assert\Url(message: ValidationException::ERROR_FIELD_INVALID)]
    #[Assert\Length(max: 5_000, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[ORM\Column(type: Types::STRING, length: 2_048)]
    #[Serialize]
    private string $premiumUrl;

    #[Assert\Url(message: ValidationException::ERROR_FIELD_INVALID)]
    #[Assert\Length(max: 2_048, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[ORM\Column(type: Types::STRING, length: 2_048, options: ['default' => ''])]
    #[Serialize]
    private string $bonusUrl;

    #[ORM\Column(type: Types::JSON)]
    #[Serialize]
    private array $authors;

    #[ORM\Column(type: Types::JSON)]
    #[Serialize]
    private array $keywords;

    #[ORM\Column(type: Types::INTEGER)]
    #[Serialize]
    private int $rubricId;

    #[ORM\Column(type: Types::STRING, length: 36)]
    #[Serialize]
    private string $episodeId;

    #[ORM\Column(type: Types::STRING, length: 36)]
    #[Serialize]
    private string $podcastId;

    public function __construct()
    {
        $this->setDescription('');
        $this->setTitle('');
        $this->setKeywords([]);
        $this->setAuthors([]);
        $this->setPremiumUrl('');
        $this->setFreeUrl('');
        $this->setExtRssId('');
        $this->setRubricId(0);
        $this->setEpisodeId('');
        $this->setPodcastId('');
        $this->setBonusUrl('');
    }

    public function getExtRssId(): string
    {
        return $this->extRssId;
    }

    public function setExtRssId(string $extRssId): self
    {
        $this->extRssId = $extRssId;

        return $this;
    }

    public function getFreeUrl(): string
    {
        return $this->freeUrl;
    }

    public function setFreeUrl(string $freeUrl): self
    {
        $this->freeUrl = $freeUrl;

        return $this;
    }

    public function getPremiumUrl(): string
    {
        return $this->premiumUrl;
    }

    public function setPremiumUrl(string $premiumUrl): self
    {
        $this->premiumUrl = $premiumUrl;

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

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function setAuthors(array $authors): self
    {
        $this->authors = $authors;

        return $this;
    }

    public function getRubricId(): int
    {
        return $this->rubricId;
    }

    public function setRubricId(int $rubricId): self
    {
        $this->rubricId = $rubricId;

        return $this;
    }

    public function getEpisodeId(): string
    {
        return $this->episodeId;
    }

    public function setEpisodeId(string $episodeId): self
    {
        $this->episodeId = $episodeId;

        return $this;
    }

    public function getPodcastId(): string
    {
        return $this->podcastId;
    }

    public function setPodcastId(string $podcastId): self
    {
        $this->podcastId = $podcastId;

        return $this;
    }

    public function getBonusUrl(): string
    {
        return $this->bonusUrl;
    }

    public function setBonusUrl(string $bonusUrl): self
    {
        $this->bonusUrl = $bonusUrl;
        return $this;
    }
}
