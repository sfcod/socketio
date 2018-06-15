<?php

namespace SfCod\SocketIoBundle\Events;

/**
 * Interface EventInterface
 * Event name and broadcast nsp
 *
 * @package SfCod\SocketIoBundle\Events
 */
interface EventInterface
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
