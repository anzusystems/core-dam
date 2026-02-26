<?php

declare(strict_types=1);

namespace App\Tests\Domain\AssetFile;

use AnzuSystems\CommonBundle\Tests\AnzuKernelTestCase;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileInternalRuleEvaluator;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Entity\DamUser;
use AnzuSystems\CoreDamBundle\Entity\Embeds\AssetFileFlags;
use AnzuSystems\CoreDamBundle\Entity\Embeds\AssetLicenceInternalRule;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\Stub;

final class AssetFileInternalRuleEvaluatorTest extends AnzuKernelTestCase
{
    private AssetFileInternalRuleEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new AssetFileInternalRuleEvaluator();
    }

    public function testEvaluateReturnsNullWhenOverrideInternalIsTrue(): void
    {
        $flags = (new AssetFileFlags())->setOverrideInternal(true);
        $asset = new Asset();
        $assetFile = $this->buildImageFileStub($flags, new AssetLicence());

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertNull($result);
    }

    public function testEvaluateReturnsNullWhenRuleIsInactive(): void
    {
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())->setActive(false);
        $licence = (new AssetLicence())->setInternalRule($internalRule);
        $asset = new Asset();
        $assetFile = $this->buildImageFileStub($flags, $licence);

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertNull($result);
    }

    public function testEvaluateReturnsTrueWhenRuleActiveAndCollectionsEmpty(): void
    {
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())->setActive(true);
        $licence = (new AssetLicence())->setInternalRule($internalRule);
        $asset = (new Asset())->setAuthors(new ArrayCollection());
        $assetFile = $this->buildImageFileStub(
            $flags,
            $licence,
            new DateTimeImmutable(),
        );

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertTrue($result);
    }

    public function testEvaluateReturnsTrueWhenAuthorMatches(): void
    {
        $authorId = '690fd785-84b1-4d3b-abdf-b986ed53c317';
        $assetAuthor = (new Author())->setId($authorId);
        $ruleAuthor = (new Author())->setId($authorId);
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())->setActive(true);
        $licence = (new AssetLicence())
            ->setInternalRule($internalRule)
            ->setInternalRuleAuthors(new ArrayCollection([$ruleAuthor]))
        ;
        $asset = (new Asset())->setAuthors(new ArrayCollection([$assetAuthor]));
        $assetFile = $this->buildImageFileStub(
            $flags,
            $licence,
            new DateTimeImmutable(),
        );

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertTrue($result);
    }

    public function testEvaluateReturnsFalseWhenAuthorDoesNotMatch(): void
    {
        $ruleAuthor = (new Author())->setId('690fd785-84b1-4d3b-abdf-b986ed53c317');
        $assetAuthor = (new Author())->setId('19a0dba5-459b-422e-ac8e-a3c1cbd20d36');
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())->setActive(true);
        $licence = (new AssetLicence())
            ->setInternalRule($internalRule)
            ->setInternalRuleAuthors(new ArrayCollection([$ruleAuthor]))
        ;
        $asset = (new Asset())->setAuthors(new ArrayCollection([$assetAuthor]));
        $assetFile = $this->buildImageFileStub(
            $flags,
            $licence,
            new DateTimeImmutable(),
        );

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertFalse($result);
    }

    public function testEvaluateReturnsTrueWhenUserMatches(): void
    {
        /** @var DamUser&Stub $ruleUser */
        $ruleUser = $this->createStub(DamUser::class);
        $ruleUser->method('getId')->willReturn(1_001);
        /** @var DamUser&Stub $fileUser */
        $fileUser = $this->createStub(DamUser::class);
        $fileUser->method('getId')->willReturn(1_001);
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())->setActive(true);
        /** @var ArrayCollection<int, DamUser> $ruleUsers */
        $ruleUsers = new ArrayCollection([$ruleUser]);
        $licence = (new AssetLicence())
            ->setInternalRule($internalRule)
            ->setInternalRuleUsers($ruleUsers)
        ;
        $asset = (new Asset())->setAuthors(new ArrayCollection());
        $assetFile = $this->buildImageFileStub(
            $flags,
            $licence,
            new DateTimeImmutable(),
            $fileUser,
        );

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertTrue($result);
    }

    public function testEvaluateReturnsFalseWhenUserDoesNotMatch(): void
    {
        /** @var DamUser&Stub $ruleUser */
        $ruleUser = $this->createStub(DamUser::class);
        $ruleUser->method('getId')->willReturn(1_001);
        /** @var DamUser&Stub $fileUser */
        $fileUser = $this->createStub(DamUser::class);
        $fileUser->method('getId')->willReturn(9_999);
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())->setActive(true);
        /** @var ArrayCollection<int, DamUser> $ruleUsers */
        $ruleUsers = new ArrayCollection([$ruleUser]);
        $licence = (new AssetLicence())
            ->setInternalRule($internalRule)
            ->setInternalRuleUsers($ruleUsers)
        ;
        $asset = (new Asset())->setAuthors(new ArrayCollection());
        $assetFile = $this->buildImageFileStub(
            $flags,
            $licence,
            new DateTimeImmutable(),
            $fileUser,
        );

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertFalse($result);
    }

    public function testEvaluateReturnsFalseWhenFileCreatedBeforeMarkAsInternalSince(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01');
        $markAsInternalSince = new DateTimeImmutable('2024-06-01');
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())
            ->setActive(true)
            ->setMarkAsInternalSince($markAsInternalSince)
        ;
        $licence = (new AssetLicence())->setInternalRule($internalRule);
        $asset = (new Asset())->setAuthors(new ArrayCollection());
        $assetFile = $this->buildImageFileStub($flags, $licence, $createdAt);

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertFalse($result);
    }

    public function testEvaluateReturnsTrueWhenFileCreatedAfterMarkAsInternalSince(): void
    {
        $createdAt = new DateTimeImmutable('2025-01-01');
        $markAsInternalSince = new DateTimeImmutable('2024-06-01');
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())
            ->setActive(true)
            ->setMarkAsInternalSince($markAsInternalSince)
        ;
        $licence = (new AssetLicence())->setInternalRule($internalRule);
        $asset = (new Asset())->setAuthors(new ArrayCollection());
        $assetFile = $this->buildImageFileStub($flags, $licence, $createdAt);

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertTrue($result);
    }

    public function testEvaluateReturnsTrueWhenAllConditionsMatch(): void
    {
        $authorId = '690fd785-84b1-4d3b-abdf-b986ed53c317';
        $assetAuthor = (new Author())->setId($authorId);
        $ruleAuthor = (new Author())->setId($authorId);
        /** @var DamUser&Stub $ruleUser */
        $ruleUser = $this->createStub(DamUser::class);
        $ruleUser->method('getId')->willReturn(1_001);
        /** @var DamUser&Stub $fileUser */
        $fileUser = $this->createStub(DamUser::class);
        $fileUser->method('getId')->willReturn(1_001);
        $createdAt = new DateTimeImmutable('2025-01-01');
        $markAsInternalSince = new DateTimeImmutable('2024-06-01');
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())
            ->setActive(true)
            ->setMarkAsInternalSince($markAsInternalSince)
        ;
        /** @var ArrayCollection<int, DamUser> $ruleUsersAll */
        $ruleUsersAll = new ArrayCollection([$ruleUser]);
        $licence = (new AssetLicence())
            ->setInternalRule($internalRule)
            ->setInternalRuleAuthors(new ArrayCollection([$ruleAuthor]))
            ->setInternalRuleUsers($ruleUsersAll)
        ;
        $asset = (new Asset())->setAuthors(new ArrayCollection([$assetAuthor]));
        $assetFile = $this->buildImageFileStub($flags, $licence, $createdAt, $fileUser);

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertTrue($result);
    }

    public function testEvaluateReturnsFalseWhenDateFailsEvenIfAuthorsAndUsersMatch(): void
    {
        $authorId = '690fd785-84b1-4d3b-abdf-b986ed53c317';
        $assetAuthor = (new Author())->setId($authorId);
        $ruleAuthor = (new Author())->setId($authorId);
        /** @var DamUser&Stub $ruleUser */
        $ruleUser = $this->createStub(DamUser::class);
        $ruleUser->method('getId')->willReturn(1_001);
        /** @var DamUser&Stub $fileUser */
        $fileUser = $this->createStub(DamUser::class);
        $fileUser->method('getId')->willReturn(1_001);
        $createdAt = new DateTimeImmutable('2023-01-01');
        $markAsInternalSince = new DateTimeImmutable('2024-06-01');
        $flags = (new AssetFileFlags())->setOverrideInternal(false);
        $internalRule = (new AssetLicenceInternalRule())
            ->setActive(true)
            ->setMarkAsInternalSince($markAsInternalSince)
        ;
        /** @var ArrayCollection<int, DamUser> $ruleUsersDate */
        $ruleUsersDate = new ArrayCollection([$ruleUser]);
        $licence = (new AssetLicence())
            ->setInternalRule($internalRule)
            ->setInternalRuleAuthors(new ArrayCollection([$ruleAuthor]))
            ->setInternalRuleUsers($ruleUsersDate)
        ;
        $asset = (new Asset())->setAuthors(new ArrayCollection([$assetAuthor]));
        $assetFile = $this->buildImageFileStub($flags, $licence, $createdAt, $fileUser);

        $result = $this->evaluator->evaluate($asset, $assetFile);

        $this->assertFalse($result);
    }

    public function testEvaluateAndApplySetsInternalFlagWhenResultIsTrue(): void
    {
        $flags = (new AssetFileFlags())
            ->setOverrideInternal(false)
            ->setInternal(false)
        ;
        $internalRule = (new AssetLicenceInternalRule())->setActive(true);
        $licence = (new AssetLicence())->setInternalRule($internalRule);
        $asset = (new Asset())->setAuthors(new ArrayCollection());
        $assetFile = $this->buildImageFileStub($flags, $licence, new DateTimeImmutable());

        $this->evaluator->evaluateAndApply($asset, $assetFile);

        $this->assertTrue($assetFile->getFlags()->isInternal());
    }

    public function testEvaluateAndApplySetsInternalFlagFalseWhenAuthorMismatches(): void
    {
        $ruleAuthor = (new Author())->setId('690fd785-84b1-4d3b-abdf-b986ed53c317');
        $assetAuthor = (new Author())->setId('19a0dba5-459b-422e-ac8e-a3c1cbd20d36');
        $flags = (new AssetFileFlags())
            ->setOverrideInternal(false)
            ->setInternal(true)
        ;
        $internalRule = (new AssetLicenceInternalRule())->setActive(true);
        $licence = (new AssetLicence())
            ->setInternalRule($internalRule)
            ->setInternalRuleAuthors(new ArrayCollection([$ruleAuthor]))
        ;
        $asset = (new Asset())->setAuthors(new ArrayCollection([$assetAuthor]));
        $assetFile = $this->buildImageFileStub(
            $flags,
            $licence,
            new DateTimeImmutable(),
        );

        $this->evaluator->evaluateAndApply($asset, $assetFile);

        $this->assertFalse($assetFile->getFlags()->isInternal());
    }

    public function testEvaluateAndApplyDoesNotChangeFlagWhenOverrideInternalIsTrue(): void
    {
        $flags = (new AssetFileFlags())
            ->setOverrideInternal(true)
            ->setInternal(false)
        ;
        $asset = new Asset();
        $assetFile = $this->buildImageFileStub($flags, new AssetLicence());

        $this->evaluator->evaluateAndApply($asset, $assetFile);

        $this->assertFalse($assetFile->getFlags()->isInternal());
    }

    private function buildImageFileStub(
        AssetFileFlags $flags,
        AssetLicence $licence,
        ?DateTimeImmutable $createdAt = null,
        ?DamUser $createdBy = null,
    ): ImageFile&Stub {
        /** @var ImageFile&Stub $imageFile */
        $imageFile = $this->createStub(ImageFile::class);
        $imageFile->method('getFlags')->willReturn($flags);
        $imageFile->method('getLicence')->willReturn($licence);
        if (null !== $createdAt) {
            $imageFile->method('getCreatedAt')->willReturn($createdAt);
        }
        if (null !== $createdBy) {
            $imageFile->method('getCreatedBy')->willReturn($createdBy);
        }

        return $imageFile;
    }
}
