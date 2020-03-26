<?php

namespace SfCod\SocketIoBundle\DependencyInjection\Compiler;

use SfCod\SocketIoBundle\Service\EventManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class EventCompilerPass.
 */
class EventCompilerPass implements CompilerPassInterface
{
    /**
     * Find all job handlers and mark them as public in case to work properly with job queue.
     */
    public function process(ContainerBuilder $container)
    {
        $eventManager = $container->getDefinition(EventManager::class);
        $taggedServices = $container->findTaggedServiceIds('sfcod.socketio.event');

        foreach ($taggedServices as $id => $tags) {
            $eventManager->addMethodCall('addEvent', [new Reference($id)]);
        }
    }
}
