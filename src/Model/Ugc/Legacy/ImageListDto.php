<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Enum\ImageCropTag;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Entity\User;
use App\Model\Ugc\Legacy\Embeds\ImageAssetFlags;
use App\Model\Ugc\Legacy\Embeds\ImageAttributes;
use App\Model\Ugc\Legacy\Embeds\ImageAuthor;
use App\Model\Ugc\Legacy\Embeds\ImageDates;
use App\Model\Ugc\Legacy\Embeds\ImageFileAttributesDto;
use App\Model\Ugc\Legacy\Embeds\ImageProcess;
use App\Model\Ugc\Legacy\Embeds\ImageTagsDto;
use App\Model\Ugc\Legacy\Embeds\ImageTextsDto;
use App\Serializer\Handler\Handlers\LegacyUgcImageLinksHandler;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;

class ImageListDto
{
    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    protected ImageFile $imageFile;

    #[Serialize]
    protected ImageTagsDto $tags;

    #[Serialize]
    protected ImageTextsDto $texts;

    #[Serialize]
    protected ImageFileAttributesDto $fileAttributes;

    #[Serialize]
    protected ImageDates $dates;

    #[Serialize]
    protected ImageProcess $process;

    #[Serialize]
    protected ImageAssetFlags $assetFlags;

    #[Serialize(type: ImageAuthor::class)]
    protected ArrayCollection $authors;

    #[Serialize]
    protected ImageAttributes $imageAttributes;

    #[Serialize]
    protected DateTimeImmutable $createdAt;

    #[Serialize]
    protected DateTimeImmutable $modifiedAt;

    #[Serialize(handler: EntityIdHandler::class)]
    protected User $createdBy;

    #[Serialize(handler: EntityIdHandler::class)]
    protected User $modifiedBy;

    public static function getInstance(ImageFile $imageFile): static
    {
        $authors = new ArrayCollection();
        $author = $imageFile->getAsset()->getMetadata()->getCustomData()['author'] ?? '';
        if ($author) {
            $authors->add(ImageAuthor::getInstance($author));
        }

        return (new static())
            ->setImageFile($imageFile)
            ->setTags(ImageTagsDto::getInstance($imageFile))
            ->setTexts(ImageTextsDto::getInstance($imageFile))
            ->setFileAttributes(ImageFileAttributesDto::getInstance($imageFile))
            ->setDates(ImageDates::getInstance($imageFile))
            ->setProcess(ImageProcess::getInstance($imageFile))
            ->setAssetFlags(ImageAssetFlags::getInstance($imageFile))
            ->setAuthors($authors)
            ->setImageAttributes(ImageAttributes::getInstance($imageFile))
            ->setCreatedAt($imageFile->getCreatedAt())
            ->setModifiedAt($imageFile->getModifiedAt())
            ->setCreatedBy($imageFile->getCreatedBy())
            ->setModifiedBy($imageFile->getModifiedBy())
        ;
    }

    public function getImageFile(): ImageFile
    {
        return $this->imageFile;
    }

    public function setImageFile(ImageFile $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    public function getTags(): ImageTagsDto
    {
        return $this->tags;
    }

    public function setTags(ImageTagsDto $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getTexts(): ImageTextsDto
    {
        return $this->texts;
    }

    public function setTexts(ImageTextsDto $texts): self
    {
        $this->texts = $texts;

        return $this;
    }

    public function getFileAttributes(): ImageFileAttributesDto
    {
        return $this->fileAttributes;
    }

    public function setFileAttributes(ImageFileAttributesDto $fileAttributes): self
    {
        $this->fileAttributes = $fileAttributes;

        return $this;
    }

    public function getDates(): ImageDates
    {
        return $this->dates;
    }

    public function setDates(ImageDates $dates): self
    {
        $this->dates = $dates;

        return $this;
    }

    public function getProcess(): ImageProcess
    {
        return $this->process;
    }

    public function setProcess(ImageProcess $process): self
    {
        $this->process = $process;

        return $this;
    }

    public function getAssetFlags(): ImageAssetFlags
    {
        return $this->assetFlags;
    }

    public function setAssetFlags(ImageAssetFlags $assetFlags): self
    {
        $this->assetFlags = $assetFlags;

        return $this;
    }

    public function getAuthors(): ArrayCollection
    {
        return $this->authors;
    }

    public function setAuthors(ArrayCollection $authors): self
    {
        $this->authors = $authors;

        return $this;
    }

    public function getImageAttributes(): ImageAttributes
    {
        return $this->imageAttributes;
    }

    public function setImageAttributes(ImageAttributes $imageAttributes): self
    {
        $this->imageAttributes = $imageAttributes;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(DateTimeImmutable $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getModifiedBy(): User
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(User $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    #[Serialize(serializedName: '_view', handler: LegacyUgcImageLinksHandler::class, type: ImageCropTag::LIST)]
    public function getLinks(): ImageFile
    {
        return $this->imageFile;
    }
}
