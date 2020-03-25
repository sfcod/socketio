<?php

namespace SfCod\SocketIoBundle;

use SfCod\QueueBundle\DependencyInjection\Compiler\JobCompilerPass;
use SfCod\SocketIoBundle\DependencyInjection\Compiler\EventCompilerPass;
use SfCod\SocketIoBundle\DependencyInjection\SocketIoExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SfCodSocketIoBundle
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\SocketIoBundle
 */
class SfCodSocketIoBundle extends Bundle
{
    /**
     * Get bundle extension
     *
     * @return null|SocketIoExtension|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new SocketIoExtension();
    }

    /**
     * Add compiler pass
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EventCompilerPass());
    }
}
