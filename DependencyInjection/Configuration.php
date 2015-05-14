<?php

namespace Dothiv\Bundle\ContentfulBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dothiv_contentful');
        $rootNode
            ->children()
                ->scalarNode('web_path')->defaultValue('/contentful')->end()
                ->scalarNode('local_path')->defaultValue('%kernel.root_dir%/../web/contentful')->end()
                ->arrayNode('webhook')
                    ->children()
                        ->scalarNode('httpBasicUsername')->defaultValue('contentful_api')->end()
                        ->scalarNode('httpBasicPassword')->isRequired()->end()
                    ->end()
                    ->isRequired()
                ->end()
            ->end();
        return $treeBuilder;
    }
}
