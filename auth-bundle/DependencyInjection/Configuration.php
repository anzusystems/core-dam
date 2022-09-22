<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\DependencyInjection;

use AnzuSystems\AuthBundle\Model\Enum\JwtAlgorithm;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('anzu_systems_auth');

        $treeBuilder->getRootNode()
            ->children()
            ->append($this->addCookieSection())
            ->append($this->addJwtSection())
            ->end()
        ;

        return $treeBuilder;
    }

    private function addCookieSection(): NodeDefinition
    {
        return (new TreeBuilder('cookie'))->getRootNode()
            ->isRequired()
            ->children()
                ->scalarNode('domain')->isRequired()->cannotBeEmpty()->end()
                ->booleanNode('secure')->isRequired()->end()
                ->arrayNode('jwt')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('lifetime')->isRequired()->defaultValue(1209600)->min(1)->end() // 2 weeks default
                        ->scalarNode('payload_part_name')->isRequired()->defaultValue('anz_jp')->end()
                        ->scalarNode('signature_part_name')->isRequired()->defaultValue('anz_js')->end()
                    ->end()
                ->end()
                ->arrayNode('refresh_token')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('lifetime')->defaultValue(31536000)->min(1)->end() // 1 year default
                        ->scalarNode('name')->defaultValue('anz_rt')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addJwtSection(): NodeDefinition
    {
        return (new TreeBuilder('jwt'))->getRootNode()
            ->isRequired()
            ->children()
                ->scalarNode('audience')->isRequired()->cannotBeEmpty()->end()
                ->enumNode('algorithm')
                    ->values(JwtAlgorithm::values())
                    ->defaultValue(JwtAlgorithm::Default->toString())
                    ->isRequired()
                ->end()
                ->scalarNode('public_cert')->isRequired()->end()
                ->scalarNode('private_cert')->isRequired()->end()
            ->end()
        ;
    }
}
