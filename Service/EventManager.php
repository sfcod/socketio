<?php

namespace SfCod\SocketIoBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EventManager
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\SocketIoBundle\Service
 */
class EventManager
{
    use ContainerAwareTrait;

    /**
     * Array of events
     *
     * @var array
     */
    protected $namespaces;

    /**
     * List with all events
     *
     * @var array
     */
    protected static $list = [];

    /**
     * EventManager constructor.
     *
     * @param ContainerInterface $container
     * @param array $namespaces
     */
    public function __construct(ContainerInterface $container, array $namespaces = [])
    {
        $this->namespaces = $namespaces;
        $this->setContainer($container);
    }

    /**
     * Get events list
     *
     * @return array
     */
    public function getList(): array
    {
        //@todo remove this, move to extension using tags
        if (empty(self::$list)) {
            foreach ($this->namespaces as $key => $namespace) {
                $alias = $this->container->getParameter('kernel.root_dir') . '/../' . str_replace('\\', DIRECTORY_SEPARATOR, trim($namespace, '\\'));

                foreach (glob(sprintf('%s/**.php', $alias)) as $file) {
                    $className = sprintf('%s\%s', $namespace, basename($file, '.php'));
                    if (method_exists($className, 'name')) {
                        self::$list[$className::name()] = $className;
                    }
                }
            }
        }

        return self::$list;
    }
}
