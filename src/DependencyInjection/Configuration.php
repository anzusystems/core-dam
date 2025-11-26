<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Model\Configuration\AssetPubConfiguration;
use App\Model\Configuration\CmsAudioProcessedAutomatConfiguration;
use App\Model\Configuration\MediaApiSyncConfiguration;
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
                ->append($this->getMediaApiConfigurationSection())
                ->append($this->getAudioDistributionSection())
                ->append($this->getRtmpConfigurationSection())
                ->append($this->getAssetPubSection())
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

    private function getMediaApiConfigurationSection(): NodeDefinition
    {
        return (new TreeBuilder('media_api'))->getRootNode()
            ->children()
                ->scalarNode(MediaApiSyncConfiguration::STORAGE_NAME)->isRequired()->end()
            ->end();
    }

    private function getAudioDistributionSection(): NodeDefinition
    {
        return (new TreeBuilder('cms_audio_processed_automat'))->getRootNode()
            ->children()
                ->scalarNode(CmsAudioProcessedAutomatConfiguration::AUDIO_FREE_SLOT_NAME_KEY)->isRequired()->end()
                ->scalarNode(CmsAudioProcessedAutomatConfiguration::AUDIO_PREMIUM_SLOT_NAME_KEY)->isRequired()->end()
                ->scalarNode(CmsAudioProcessedAutomatConfiguration::AUDIO_BONUS_SLOT_NAME_KEY)->isRequired()->end()
            ->end();
    }

    private function getAssetPubSection(): NodeDefinition
    {
        return (new TreeBuilder('asset_pub_configuration'))->getRootNode()
            ->children()
                ->scalarNode(AssetPubConfiguration::METADATA_TITLE)->isRequired()->end()
                ->arrayNode(AssetPubConfiguration::VIDEO_ALLOWED_DISTRIBUTIONS)
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }
}
