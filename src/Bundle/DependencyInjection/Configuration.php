<?php

declare(strict_types=1);

namespace Sourceability\Portal\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sourceability_portal');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('openai_api_key')
                    ->defaultValue('%env(OPENAI_API_KEY)%')
                ->end()
                ->arrayNode('httplug_plugins')
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
