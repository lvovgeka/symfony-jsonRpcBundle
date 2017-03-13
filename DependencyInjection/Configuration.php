<?php

namespace Lvovgeka\JsonRpcBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lvovgeka_json_rpc');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                ->variableNode('mapping')
                    ->defaultValue(null)
                ->end()
                 ->arrayNode('cache')
                    ->addDefaultsIfNotSet(['driver'=>'file','options' => ['directory' => '%kernel.cache_dir%/rpc']])
                    ->children()
                        ->variableNode('driver')->defaultValue('file')->end()
                        ->variableNode('options')->defaultValue(['directory' => '%kernel.cache_dir%/rpc'])
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
