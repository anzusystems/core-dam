<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Model\Ugc\Legacy\Embeds\ImageAuthorDto;
use App\Model\Ugc\Legacy\Embeds\ImageTextsUpdateDto;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageUpdateDto
{
    #[Serialize]
    private string $id;

    #[Serialize]
    #[Assert\Valid]
    private ImageTextsUpdateDto $texts;

    #[Serialize]
    #[Assert\Valid]
    private ImageAuthorDto $author;

    public function __construct()
    {
        $this->setId('');
        $this->setTexts(new ImageTextsUpdateDto());
        $this->setAuthor(new ImageAuthorDto());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTexts(): ImageTextsUpdateDto
    {
        return $this->texts;
    }

    public function setTexts(ImageTextsUpdateDto $texts): self
    {
        $this->texts = $texts;

        return $this;
    }

    public function getAuthor(): ImageAuthorDto
    {
        return $this->author;
    }

    public function setAuthor(ImageAuthorDto $author): self
    {
        $this->author = $author;

        return $this;
    }
}
