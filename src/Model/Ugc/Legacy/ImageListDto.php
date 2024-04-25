<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Enum\ApiViewType;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Entity\User;
use App\Model\Ugc\Legacy\Embeds\ImageAssetFlagsDto;
use App\Model\Ugc\Legacy\Embeds\ImageAttributesDto;
use App\Model\Ugc\Legacy\Embeds\ImageAuthorDto;
use App\Model\Ugc\Legacy\Embeds\ImageDatesDto;
use App\Model\Ugc\Legacy\Embeds\ImageFileAttributesDto;
use App\Model\Ugc\Legacy\Embeds\ImageProcessDto;
use App\Model\Ugc\Legacy\Embeds\ImageTagsDto;
use App\Model\Ugc\Legacy\Embeds\ImageTextsDto;
use App\Serializer\Handler\Handlers\LegacyUgcImageLinksHandler;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;

class ImageListDto
{
    protected ImageFile $imageFile;
    protected ImageTagsDto $tags;
    protected ImageTextsDto $texts;
    protected ImageFileAttributesDto $fileAttributes;
    protected ImageDatesDto $dates;
    protected ImageProcessDto $process;
    protected ImageAssetFlagsDto $assetFlags;
    protected ArrayCollection $authors;
    protected ImageAttributesDto $imageAttributes;
    protected DateTimeImmutable $createdAt;
    protected DateTimeImmutable $modifiedAt;
    protected User $createdBy;
    protected User $modifiedBy;

    public static function getInstance(ImageFile $imageFile): static
    {
        /** @var ArrayCollection<int, ImageAuthorDto> $authors */
        $authors = new ArrayCollection();
        $author = $imageFile->getAsset()->getMetadata()->getCustomData()['author'] ?? '';
        if ($author) {
            $authors->add(ImageAuthorDto::getInstance($author));
        }

        /** @var User $createdBy */
        $createdBy = $imageFile->getCreatedBy();
        /** @var User $modifiedBy */
        $modifiedBy = $imageFile->getModifiedBy();

        return (new static())
            ->setImageFile($imageFile)
            ->setTags(ImageTagsDto::getInstance($imageFile))
            ->setTexts(ImageTextsDto::getInstance($imageFile))
            ->setFileAttributes(ImageFileAttributesDto::getInstance($imageFile))
            ->setDates(ImageDatesDto::getInstance($imageFile))
            ->setProcess(ImageProcessDto::getInstance($imageFile))
            ->setAssetFlags(ImageAssetFlagsDto::getInstance($imageFile))
            ->setAuthors($authors)
            ->setImageAttributes(ImageAttributesDto::getInstance($imageFile))
            ->setCreatedAt($imageFile->getCreatedAt())
            ->setModifiedAt($imageFile->getModifiedAt())
            ->setCreatedBy($createdBy)
            ->setModifiedBy($modifiedBy)
        ;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getImageFile(): ImageFile
    {
        return $this->imageFile;
    }

    public function setImageFile(ImageFile $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    #[Serialize]
    public function getTags(): ImageTagsDto
    {
        return $this->tags;
    }

    public function setTags(ImageTagsDto $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    #[Serialize]
    public function getTexts(): ImageTextsDto
    {
        return $this->texts;
    }

    public function setTexts(ImageTextsDto $texts): self
    {
        $this->texts = $texts;

        return $this;
    }

    #[Serialize]
    public function getFileAttributes(): ImageFileAttributesDto
    {
        return $this->fileAttributes;
    }

    public function setFileAttributes(ImageFileAttributesDto $fileAttributes): self
    {
        $this->fileAttributes = $fileAttributes;

        return $this;
    }

    #[Serialize]
    public function getDates(): ImageDatesDto
    {
        return $this->dates;
    }

    public function setDates(ImageDatesDto $dates): self
    {
        $this->dates = $dates;

        return $this;
    }

    #[Serialize]
    public function getProcess(): ImageProcessDto
    {
        return $this->process;
    }

    public function setProcess(ImageProcessDto $process): self
    {
        $this->process = $process;

        return $this;
    }

    #[Serialize]
    public function getAssetFlags(): ImageAssetFlagsDto
    {
        return $this->assetFlags;
    }

    public function setAssetFlags(ImageAssetFlagsDto $assetFlags): self
    {
        $this->assetFlags = $assetFlags;

        return $this;
    }

    /**
     * @return ArrayCollection<int, ImageAuthorDto>
     */
    #[Serialize(type: ImageAuthorDto::class)]
    public function getAuthors(): ArrayCollection
    {
        return $this->authors;
    }

    /**
     * @param ArrayCollection<int, ImageAuthorDto> $authors
     */
    public function setAuthors(ArrayCollection $authors): self
    {
        $this->authors = $authors;

        return $this;
    }

    #[Serialize]
    public function getImageAttributes(): ImageAttributesDto
    {
        return $this->imageAttributes;
    }

    public function setImageAttributes(ImageAttributesDto $imageAttributes): self
    {
        $this->imageAttributes = $imageAttributes;

        return $this;
    }

    #[Serialize]
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[Serialize]
    public function getModifiedAt(): DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(DateTimeImmutable $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    #[Serialize(handler: EntityIdHandler::class)]
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    #[Serialize(handler: EntityIdHandler::class)]

    public function getModifiedBy(): User
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(User $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    #[Serialize(serializedName: '_view', handler: LegacyUgcImageLinksHandler::class, type: ApiViewType::LIST)]
    public function getLinks(): ImageFile
    {
        return $this->imageFile;
    }
}
