<?php

namespace SfCod\SocketIoBundle\Service;

use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\Exception\EventDuplicateException;
use SfCod\SocketIoBundle\Exception\EventNotFoundException;

/**
 * Class EventManager.
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\SocketIoBundle\Service
 */
class EventManager
{
    /**
     * List with all events.
     *
     * @var array
     */
    protected $list = [];
    /**
     * Array of events.
     *
     * @var array
     */
    protected $namespaces;
    /**
     * Project root directory.
     *
     * @var string
     */
    protected $rootDir;

    /**
     * EventManager constructor.
     */
    public function __construct(string $rootDir, array $namespaces = [])
    {
        $this->rootDir = $rootDir;
        $this->namespaces = $namespaces;
    }

    /**
     * Get events list.
     */
    public function getList(): array
    {
        //@todo remove this, move to extension using tags
        if (empty(self::$list)) {
            foreach ($this->namespaces as $key => $namespace) {
                $alias = $this->rootDir . '/../' . str_replace('\\', DIRECTORY_SEPARATOR, trim($namespace, '\\'));

                foreach (glob(sprintf('%s/**.php', $alias)) as $file) {
                    $className = sprintf('%s\%s', $namespace, basename($file, '.php'));
                    if (method_exists($className, 'name')) {
                        $this->list[$className::name()] = $className;
                    }
                }
            }
        }

        return $this->list;
    }

    /**
     * Resolve the given class.
     */
    public function resolve(string $name): EventInterface
    {
        if (isset($this->list[$name])) {
            return $this->list[$name];
        }

        throw new EventNotFoundException("Event handler '$name' not found.");
    }

    /**
     * @param string $id
     */
    public function addEvent(EventInterface $event)
    {
        if (isset($this->list[$event::name()])) {
            throw new EventDuplicateException("Event '$name' already exists.");
        }

        $this->list[$event::name()] = $event;
    }
}
