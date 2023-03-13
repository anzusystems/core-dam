<?php

declare(strict_types=1);

namespace App\Entity\Embeds;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class ArtemisVideoTexts
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    #[Assert\Length(max: 100, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[Serialize]
    private string $title;

    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    #[Assert\Length(max: 5_000, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[ORM\Column(type: Types::STRING, length: 5_000)]
    #[Serialize]
    private string $description;

    #[ORM\Column(type: Types::JSON)]
    #[Serialize]
    private array $authors;

    #[ORM\Column(type: Types::JSON)]
    #[Serialize]
    private array $keywords;

    #[ORM\Column(type: Types::INTEGER)]
    #[Serialize]
    private int $rubricId;

    public function __construct()
    {
        $this->setDescription('');
        $this->setTitle('');
        $this->setKeywords([]);
        $this->setAuthors([]);
        $this->setRubricId(0);
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
}
