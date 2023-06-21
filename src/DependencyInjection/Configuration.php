<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use AnzuSystems\CoreDamBundle\DependencyInjection\Configuration as BaseConfiguration;
use App\Model\Configuration\ArtemisAudioDistributionConfiguration;
use App\Model\Configuration\ArtemisVideoDistributionConfiguration;
use App\Model\Configuration\RtmpConfiguration;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('core_dam');
        $treeBuilder->getRootNode()
            ->children()
            ->append($this->getAudioDistributionSection())
            ->append($this->getVideoDistributionSection())
            ->append($this->getRtmpConfigurationSection())
            ->end();

        return $treeBuilder;
    }

    private function getRtmpConfigurationSection(): NodeDefinition
    {
        return (new TreeBuilder('rtmp'))->getRootNode()
            ->children()
                ->scalarNode(RtmpConfiguration::STORAGE_NAME)->isRequired()->end()
                ->integerNode(RtmpConfiguration::ASSET_LICENCE_ID)->isRequired()->end()
                ->scalarNode(RtmpConfiguration::KEYWORD_ID)->end()
                ->scalarNode(RtmpConfiguration::TITLE_TEMPLATE)->end()
            ->end();
    }

    private function getAudioDistributionSection(): NodeDefinition
    {
        return (new TreeBuilder('artemis_audio_distribution'))->getRootNode()
            ->children()
                ->scalarNode(ArtemisAudioDistributionConfiguration::AUDIO_FREE_SLOT_NAME_KEY)->isRequired()->end()
                ->scalarNode(ArtemisAudioDistributionConfiguration::AUDIO_PREMIUM_SLOT_NAME_KEY)->isRequired()->end()
                ->scalarNode(ArtemisAudioDistributionConfiguration::AUDIO_BONUS_SLOT_NAME_KEY)->isRequired()->end()
                ->integerNode(ArtemisAudioDistributionConfiguration::DEFAULT_RUBRIC_ID)->isRequired()->end()
                ->booleanNode(ArtemisAudioDistributionConfiguration::RSS_JW_DISTRIBUTE)->isRequired()->end()
                ->append(BaseConfiguration::addTextMapperConfiguration(
                    ArtemisAudioDistributionConfiguration::CUSTOM_DATA_TO_DISTRIBUTION_MAP
                ))
            ->end();
    }

    private function getVideoDistributionSection(): NodeDefinition
    {
        return (new TreeBuilder('artemis_video_distribution'))->getRootNode()
            ->children()
            ->integerNode(ArtemisVideoDistributionConfiguration::DEFAULT_RUBRIC_ID)->isRequired()->end()
            ->append(BaseConfiguration::addTextMapperConfiguration(
                ArtemisVideoDistributionConfiguration::CUSTOM_DATA_TO_DISTRIBUTION_MAP
            ))
            ->end();
    }
}
