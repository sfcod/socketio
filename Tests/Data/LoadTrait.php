<?php

namespace SfCod\SocketIoBundle\Tests\Data;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use SfCod\SocketIoBundle\DependencyInjection\SocketIoExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait LoadTrait
{
    protected $container;

    protected function configure()
    {
        $extension = new SocketIoExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', '');
        $container->setParameter('kernel.root_dir', realpath(__DIR__ . '/../../../../SfCod/'));
        $container->setParameter('kernel.bundles', []);
        $container->set(LoggerInterface::class, new Logger('test'));

        $extension->load([
            0 => [
                'namespaces' => [
                    'SfCod\SocketIoBundle\Tests\Data',
                ],
            ],
        ], $container);

        $this->container = $container;
    }
}
