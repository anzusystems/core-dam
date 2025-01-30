<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Domain\EntityIterator\EntityIterator;
use AnzuSystems\CoreDamBundle\Domain\EntityIterator\Model\EntityIteratorConfig;
use AnzuSystems\CoreDamBundle\Domain\EntityIterator\Visitor\EntityIteratorOnBatchFlushVisitor;
use AnzuSystems\CoreDamBundle\Elasticsearch\ElasticSearch;
use AnzuSystems\CoreDamBundle\Elasticsearch\SearchDto\AssetAdmSearchDto;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Repository\ExtSystemRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'anzu:sibling:pair:audio-video',
    description: 'Pair audio/video siblings by title'
)]
final class PairAudioVideoSiblingsCommand extends Command
{
    public function __construct(
        private readonly ElasticSearch $elasticSearch,
        private readonly ExtSystemRepository $extSystemRepository,
        private readonly EntityIterator $entityIterator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extSystem = $this->extSystemRepository->findOneBySlug('cms');
        if (null === $extSystem) {
            $output->writeln('Ext system not found');

            return Command::FAILURE;
        }
        $config = (new EntityIteratorConfig(batch: 100, useOnBatchVisitor: EntityIteratorOnBatchFlushVisitor::class));

        /** @var PodcastEpisode $entity */
        foreach ($this->entityIterator->iterateEntities(PodcastEpisode::class, $config) as $entity) {
            try {
                $asset = $entity->getAsset();
                if (null === $asset) {
                    continue;
                }
                $title = $asset->getMetadata()->getCustomData()['title'] ?? '';

                $res = $this->elasticSearch->searchInfiniteList(
                    (new AssetAdmSearchDto())
                        ->setCustomDataKey('title')
                        ->setCustomDataValue($title)
                        ->setType([AssetType::VIDEO]),
                    $extSystem
                );

                /** @var Asset|null $targetAsset */
                $targetAsset = array_values($res->getData())[0] ?? null;

                if (null === $targetAsset) {
                    continue;
                }

                $targetAssetTitle = $targetAsset->getMetadata()->getCustomData()['title'] ?? '';

                if (false === ($title === $targetAssetTitle)) {
                    continue;
                }

                $targetAsset->getSiblingToAsset()?->setSiblingToAsset(null);
                $targetAsset->setSiblingToAsset($asset);
                $asset->setSiblingToAsset($targetAsset);
            } catch (Throwable) {
                $output->writeln('Direct source url not found for ' . $entity->getId());
            }
        }

        return Command::SUCCESS;
    }
}
