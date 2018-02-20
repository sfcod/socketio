<?php

namespace SfCod\SocketIoBundle\DependencyInjection;

use Psr\Log\LoggerInterface;
use SfCod\SocketIoBundle\Command\NodeJsServerCommand;
use SfCod\SocketIoBundle\Command\PhpServerCommand;
use SfCod\SocketIoBundle\Command\ProcessCommand;
use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\Events\EventPublisherInterface;
use SfCod\SocketIoBundle\Events\EventSubscriberInterface;
use SfCod\SocketIoBundle\Service\Broadcast;
use SfCod\SocketIoBundle\Service\EventManager;
use SfCod\SocketIoBundle\Service\Process;
use SfCod\SocketIoBundle\Service\RedisDriver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SocketIoExtension
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\SocketIoBundle\DependencyInjection
 */
class SocketIoExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new SocketIoConfiguration();

        $config = $this->processConfiguration($configuration, $configs);

        $container->registerForAutoconfiguration(EventPublisherInterface::class)
            ->addTag('socketio.publisher');
        $container->registerForAutoconfiguration(EventSubscriberInterface::class)
            ->addTag('socketio.subscriber');
        $container->registerForAutoconfiguration(EventInterface::class)
            ->addTag('socketio.events');

        $this->createDriver($config, $container);
        $this->createBroadcast($config, $container);
        $this->createEventManager($config, $container);
        $this->createProcess($config, $container);
        $this->createCommands($config, $container);

        $eventManager = $container->get(EventManager::class);

        foreach ($eventManager->getList() as $name => $class) {
            $definition = new Definition($class);
            $definition
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(true);
            $container->setDefinition(sprintf('socketio.%s', $name), $definition);
        }
    }

    /**
     * Get extension alias
     *
     * @return string
     */
    public function getAlias()
    {
        return 'sfcod_socketio';
    }

    /**
     * Create command
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function createCommands(array $config, ContainerBuilder $container)
    {
        $nodeJs = new Definition(NodeJsServerCommand::class);
        $nodeJs->addTag('console.command');

        $phpServer = new Definition(PhpServerCommand::class);
        $phpServer->addTag('console.command');

        $process = new Definition(ProcessCommand::class);
        $process->addTag('console.command');

        $container->addDefinitions([
            PhpServerCommand::class => $phpServer,
            ProcessCommand::class => $process,
            NodeJsServerCommand::class => $nodeJs,
        ]);
    }

    /**
     * Create driver
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function createDriver(array $config, ContainerBuilder $container)
    {
        $redis = new Definition(RedisDriver::class);
        $redis->setPublic(true);
        $redis->setArguments([
            getenv('REDIS_URL'),
        ]);

        $container->setDefinition(RedisDriver::class, $redis);
    }

    /**
     * Create broadcast
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function createBroadcast(array $config, ContainerBuilder $container)
    {
        $broadcast = new Definition(Broadcast::class);
        $broadcast->setPublic(true);
        $broadcast->setArguments([
            new Reference(ContainerInterface::class),
            new Reference(RedisDriver::class),
            new Reference(EventManager::class),
            new Reference(LoggerInterface::class),
            new Reference(Process::class),
        ]);

        $container->setDefinition(Broadcast::class, $broadcast);
    }

    /**
     * Create event manager
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function createEventManager(array $config, ContainerBuilder $container)
    {
        $eventManager = new Definition(EventManager::class);
        $eventManager->setPublic(true);
        $eventManager->setArguments([
            new Reference(ContainerInterface::class),
            $config['namespaces'],
        ]);

        $container->setDefinition(EventManager::class, $eventManager);
    }

    /**
     * Create process
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function createProcess(array $config, ContainerBuilder $container)
    {
        $jobProcess = new Definition(Process::class);
        $jobProcess->setPublic(true);
        $jobProcess->setArguments([
            'console',
            sprintf('%s/bin', $container->getParameter('kernel.project_dir')),
        ]);

        $container->setDefinition(Process::class, $jobProcess);
    }
}
