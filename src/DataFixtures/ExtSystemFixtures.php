<?php

declare(strict_types=1);


namespace App\DataFixtures;

use Anzu\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\CoreDamBundle\Domain\ExtSystem\ExtSystemManager;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use Generator;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractFixtures<ExtSystem>
 */
final class ExtSystemFixtures extends AbstractFixtures
{
    public const DEFAULT_EXT_SYSTEM_ID = 1;

    public function __construct(
        private readonly ExtSystemManager $extSystemManager,
    ) {
    }

    public static function getIndexKey(): string
    {
        return ExtSystem::class;
    }

    public function load(ProgressBar $progressBar): void
    {
        $this->configureAssignedGenerator();
        /** @var ExtSystem $extSystem */
        foreach ($progressBar->iterate($this->getData()) as $extSystem) {
            $extSystem = $this->extSystemManager->create($extSystem);
            $this->addToRegistry($extSystem, (int) $extSystem->getId());
        }
    }

    private function getData(): Generator
    {
        yield (new ExtSystem())
            ->setId(self::DEFAULT_EXT_SYSTEM_ID)
            ->setName('CMS system')
            ->setSlug('cms');
    }
}