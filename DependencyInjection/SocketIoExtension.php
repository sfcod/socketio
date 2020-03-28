<?php

namespace SfCod\SocketIoBundle\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SfCod\SocketIoBundle\Command\NodeJsServerCommand;
use SfCod\SocketIoBundle\Command\PhpServerCommand;
use SfCod\SocketIoBundle\Command\ProcessCommand;
use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\Events\EventPublisherInterface;
use SfCod\SocketIoBundle\Events\EventSubscriberInterface;
use SfCod\SocketIoBundle\Middleware\Process\DoctrineReconnect;
use SfCod\SocketIoBundle\Service\Broadcast;
use SfCod\SocketIoBundle\Service\EventManager;
use SfCod\SocketIoBundle\Service\Process;
use SfCod\SocketIoBundle\Service\RedisDriver;
use SfCod\SocketIoBundle\Service\Worker;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SocketIoExtension.
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
        $this->createWorker($config, $container);
        $this->createCommands($config, $container);

        $eventManager = $container->get(EventManager::class);

        foreach ($eventManager->getList() as $name => $class) {
            $definition = new Definition($class);
            $definition
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(false);
            $eventManager->addMethodCall('addEvent', [$definition]);
        }
    }

    /**
     * Create driver.
     */
    private function createDriver(array $config, ContainerBuilder $container)
    {
        $redis = new Definition(RedisDriver::class);
        $redis->setArguments([
            $container->getParameter('env(REDIS_URL)'),
        ]);

        $container->setDefinition(RedisDriver::class, $redis);
    }

    /**
     * Create broadcast.
     */
    private function createBroadcast(array $config, ContainerBuilder $container)
    {
        $broadcast = new Definition(Broadcast::class);
        $broadcast->setArguments([
            new Reference(RedisDriver::class),
            new Reference(EventManager::class),
            new Reference(LoggerInterface::class),
            new Reference(Process::class),
        ]);

        if ($container->getParameter('kernel.bundles')['DoctrineBundle'] ?? null) {
            $doctrineReconnect = new Definition(DoctrineReconnect::class);
            $doctrineReconnect->setArguments([
                new Reference(EntityManagerInterface::class),
            ]);

            $container->setDefinition(DoctrineReconnect::class, $doctrineReconnect);
        }

        if (isset($config['processMiddlewares'])) {
            $processMiddlewares = [];
            foreach ($config['processMiddlewares'] as $processMiddlewareId) {
                if (!$container->has($processMiddlewareId)) {
                    throw new RuntimeException(sprintf('Invalid middleware: service "%s" not found.', $processMiddlewareId));
                }
                $processMiddlewares[] = new Reference($processMiddlewareId);
            }

            $broadcast->addMethodCall('setProcessMiddlewares', [$processMiddlewares]);
        }

        $container->setDefinition(Broadcast::class, $broadcast);
    }

    /**
     * Create event manager.
     */
    private function createEventManager(array $config, ContainerBuilder $container)
    {
        $eventManager = new Definition(EventManager::class);
        $eventManager->setArguments([
            $container->getParameter('kernel.root_dir'),
            $config['namespaces'],
        ]);

        $container->setDefinition(EventManager::class, $eventManager);
    }

    /**
     * Create process.
     */
    private function createProcess(array $config, ContainerBuilder $container)
    {
        $jobProcess = new Definition(Process::class);
        $jobProcess->setArguments([
            'console',
            sprintf('%s/bin', $container->getParameter('kernel.project_dir')),
        ]);

        $container->setDefinition(Process::class, $jobProcess);
    }

    /**
     * Create worker.
     *
     * @throws \Exception
     */
    private function createWorker(array $config, ContainerBuilder $container)
    {
        $worker = new Definition(Worker::class);
        $worker->setArguments([
            new Reference(EventManager::class),
            new Reference(RedisDriver::class),
            new Reference(Broadcast::class),
            $container->hasParameter('kernel.logs_dir') ?
                $container->getParameter('kernel.logs_dir') . '/socketio' :
                $container->getParameter('kernel.root_dir') . '/../../var/log/socketio',
        ]);

        $container->setDefinition(Worker::class, $worker);
    }

    /**
     * Create command.
     */
    private function createCommands(array $config, ContainerBuilder $container)
    {
        $nodeJs = new Definition(NodeJsServerCommand::class);
        $nodeJs->setArguments([
            new Reference(Worker::class),
        ]);
        $nodeJs->addTag('console.command');

        $phpServer = new Definition(PhpServerCommand::class);
        $phpServer->setArguments([
            new Reference(Worker::class),
        ]);
        $phpServer->addTag('console.command');

        $process = new Definition(ProcessCommand::class);
        $process->setArguments([
            new Reference(Broadcast::class),
        ]);
        $process->addTag('console.command');

        $container->addDefinitions([
            PhpServerCommand::class => $phpServer,
            ProcessCommand::class => $process,
            NodeJsServerCommand::class => $nodeJs,
        ]);
    }

    /**
     * Get extension alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return 'sfcod_socketio';
    }
}
