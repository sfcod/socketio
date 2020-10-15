<?php

namespace SfCod\SocketIoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class SocketIoConfiguration.
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
        $treeBuilder = new TreeBuilder('sfcod_socketio');
        $rootNode = $treeBuilder->getRootNode();

        $this->addNamespaces($rootNode);

        return $treeBuilder;
    }

    /**
     * Add namespaces.
     */
    private function addNamespaces(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('namespaces')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('processMiddlewares')
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }
}
