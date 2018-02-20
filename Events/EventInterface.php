<?php

namespace SfCod\SocketIoBundle\Events;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Interface EventInterface
 * Event name and broadcast nsp
 *
 * @package SfCod\SocketIoBundle\Events
 */
interface EventInterface extends ContainerAwareInterface
{
    /**
     * List broadcast nsp array
     *
     * @return array
     */
    public static function broadcastOn(): array;

    /**
     * @param array $payload
     *
     * @return EventInterface
     */
    public function setPayload($data): EventInterface;

    /**
     * Get event name
     *
     * @return string
     */
    public static function name(): string;
}
