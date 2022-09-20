<?php

declare(strict_types=1);


namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\CoreDamBundle\Domain\CustomForm\CustomFormManager;
use AnzuSystems\CoreDamBundle\Entity\CustomForm;
use AnzuSystems\CoreDamBundle\Entity\CustomFormElement;
use AnzuSystems\CoreDamBundle\Entity\Embeds\CustomFormElementAttributes;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Model\Enum\CustomFormElementType;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractFixtures<CustomForm>
 */
final class CustomFormFixtures extends AbstractFixtures
{
    public function __construct(
        private readonly CustomFormManager $customFormManager,
    ) {
    }

    public static function getIndexKey(): string
    {
        return CustomForm::class;
    }

    public static function getDependencies(): array
    {
        return [ExtSystemFixtures::class];
    }

    public function load(ProgressBar $progressBar): void
    {
        /** @var CustomForm $customForm */
        foreach ($progressBar->iterate($this->getData()) as $customForm) {
            $customForm = $this->customFormManager->create($customForm);
            $this->addToRegistry($customForm, (int)$customForm->getId());
        }
    }

    private function getData(): Generator
    {
        $extSystem = $this->entityManager->getPartialReference(
            ExtSystem::class,
            ExtSystemFixtures::DEFAULT_EXT_SYSTEM_ID
        );

        yield $this->createImageCustomForm($extSystem);
        yield $this->createAudioCustomForm($extSystem);
        yield $this->createVideoCustomForm($extSystem);
        yield $this->createDocumentCustomForm($extSystem);
    }

    private function createImageCustomForm(ExtSystem $extSystem): CustomForm
    {
        return (new CustomForm())
            ->setAssetType(AssetType::Image)
            ->setExtSystem($extSystem)
            ->setElements(
                new ArrayCollection([
                    (new CustomFormElement())
                        ->setKey('title')
                        ->setName('Title')
                        ->setPosition(0)
                        ->setAttributes(
                            (new CustomFormElementAttributes())
                                ->setSearchable(true)
                                ->setType(CustomFormElementType::String)
                                ->setMaxValue(256)
                                ->setRequired(true)
                        ),
                    (new CustomFormElement())
                        ->setKey('description')
                        ->setName('Description')
                        ->setPosition(1)
                        ->setAttributes(
                            (new CustomFormElementAttributes())
                                ->setType(CustomFormElementType::String)
                                ->setMaxValue(2000)
                        ),
                    (new CustomFormElement())
                        ->setKey('year')
                        ->setName('Year')
                        ->setPosition(2)
                        ->setAttributes(
                            (new CustomFormElementAttributes())
                                ->setType(CustomFormElementType::Number)
                                ->setMinValue(1990)
                                ->setMAxValue(2050)
                        ),
                    (new CustomFormElement())
                        ->setKey('keywords')
                        ->setName('Keywords')
                        ->setPosition(3)
                        ->setAttributes(
                            (new CustomFormElementAttributes())
                                ->setType(CustomFormElementType::Array)
                                ->setMinValue(3)
                                ->setMaxValue(64)
                                ->setMinCount(1)
                                ->setMaxCount(5)
                        )
                ])
            );
    }

    private function createAudioCustomForm(ExtSystem $extSystem): CustomForm
    {
        return (new CustomForm())
            ->setAssetType(AssetType::Audio)
            ->setExtSystem($extSystem)
            ->setElements(
                new ArrayCollection([
                    (new CustomFormElement())
                        ->setKey('title')
                        ->setName('Title')
                        ->setPosition(0)
                        ->setAttributes(
                            (new CustomFormElementAttributes())
                                ->setType(CustomFormElementType::String)
                                ->setMaxValue(128)
                                ->setRequired(true)
                        ),
                    (new CustomFormElement())
                        ->setKey('description')
                        ->setName('Description')
                        ->setPosition(1)
                        ->setAttributes(
                            (new CustomFormElementAttributes())
                                ->setType(CustomFormElementType::String)
                                ->setMaxValue(2000)
                        )
                ])
            );
    }

    private function createDocumentCustomForm(ExtSystem $extSystem): CustomForm
    {
        return (new CustomForm())
            ->setAssetType(AssetType::Document)
            ->setExtSystem($extSystem)
            ->setElements(
                new ArrayCollection([
                    (new CustomFormElement())
                        ->setKey('title')
                        ->setName('Title')
                        ->setPosition(0)
                        ->setAttributes(
                            (new CustomFormElementAttributes())
                                ->setType(CustomFormElementType::String)
                                ->setMaxValue(256)
                                ->setRequired(true)
                        ),
                ])
            );
    }

    private function createVideoCustomForm(ExtSystem $extSystem): CustomForm
    {
        return (new CustomForm())
            ->setAssetType(AssetType::Video)
            ->setExtSystem($extSystem)
            ->setElements(
                new ArrayCollection([
                    (new CustomFormElement())
                        ->setKey('title')
                        ->setName('Title')
                        ->setPosition(0)
                        ->setAttributes(
                            (new CustomFormElementAttributes())
                                ->setType(CustomFormElementType::String)
                                ->setMaxValue(256)
                                ->setRequired(true)
                        ),
                    (new CustomFormElement())
                        ->setKey('description')
                        ->setName('Description')
                        ->setPosition(1)
                        ->setAttributes(
                            (new CustomFormElementAttributes())
                                ->setType(CustomFormElementType::String)
                                ->setMaxValue(5000)
                        )
                ])
            );
    }
}