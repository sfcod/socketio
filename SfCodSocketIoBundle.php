<?php

namespace SfCod\SocketIoBundle;

use SfCod\SocketIoBundle\DependencyInjection\SocketIoExtension;
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
}
