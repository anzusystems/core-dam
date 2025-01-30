<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Distribution\Modules\JwVideo\JwDirectSourceUrlProvider;
use AnzuSystems\CoreDamBundle\Domain\Configuration\DistributionConfigurationProvider;
use AnzuSystems\CoreDamBundle\Domain\EntityIterator\EntityIterator;
use AnzuSystems\CoreDamBundle\Domain\EntityIterator\Model\EntityIteratorConfig;
use AnzuSystems\CoreDamBundle\Domain\EntityIterator\Visitor\EntityIteratorOnBatchFlushVisitor;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'anzu:jw-player:update-direct-source-url',
    description: 'Fetch direct source url for JW Player'
)]
final class JwPlayerUpdateDirectSourceUrlCommand extends Command
{
    public function __construct(
        private readonly EntityIterator $entityIterator,
        private readonly JwDirectSourceUrlProvider $jwDirectSourceUrlProvider,
        protected DistributionConfigurationProvider $distributionConfigurationProvider,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jwDistributionConfig = $this->distributionConfigurationProvider->getJwDistributionService('jw_cms');
        $config = (new EntityIteratorConfig(batch: 100, useOnBatchVisitor: EntityIteratorOnBatchFlushVisitor::class));

        /** @var JwDistribution $entity */
        foreach ($this->entityIterator->iterateEntities(JwDistribution::class, $config) as $entity) {
            try {
                if ($entity->getStatus()->isNot(DistributionProcessStatus::Distributed)) {
                    continue;
                }
                $this->jwDirectSourceUrlProvider->provideDirectSourceUrl($jwDistributionConfig, $entity);
                usleep(350);
            } catch (Throwable) {
                $output->writeln('Direct source url not found for ' . $entity->getId());
            }
        }

        return Command::SUCCESS;
    }
}
