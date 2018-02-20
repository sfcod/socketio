<?php

namespace SfCod\SocketIoBundle\Tests\Data;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use SfCod\SocketIoBundle\DependencyInjection\SocketIoExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;

trait LoadTrait
{
    protected $container;

    protected function configure()
    {
        $dotenv = new Dotenv();
        try {
            $dotenv->load(__DIR__ . '/../../.env');
        } catch (PathException $e) {
            // Nothing
        }

        $extension = new SocketIoExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', getenv('KERNEL_PROJECT_DIR'));
        $container->setParameter('kernel.root_dir', realpath(__DIR__ . '/../../../../SfCod/'));
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
