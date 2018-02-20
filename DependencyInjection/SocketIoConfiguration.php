<?php

namespace SfCod\SocketIoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class SocketIoConfiguration
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\SocketIoBundle\DependencyInjection
 */
class SocketIoConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sfcod_socketio');

        $this->addNamespaces($rootNode);

        return $treeBuilder;
    }

    /**
     * Add namespaces
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addNamespaces(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('namespaces')
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }
}
