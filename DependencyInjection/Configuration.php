<?php

namespace Fidesio\IsidoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fidesio_isidore');

        $rootNode
            ->children()
                ->arrayNode('client')
                    ->children()
                        ->scalarNode('url')->end()
                        ->scalarNode('login')->end()
                        ->scalarNode('password')->end()
                        ->scalarNode('auth_basic_user')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('auth_basic_pass')
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->children()
                        ->booleanNode('enable')->defaultFalse()->end()
                        ->enumNode('type')->values(['file', 'redis'])->end()
                        ->scalarNode('redis')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
