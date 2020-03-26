<?php

namespace SfCod\SocketIoBundle\Events;

/**
 * Interface EventInterface
 * Event name and broadcast nsp.
 *
 * @package SfCod\SocketIoBundle\Events
 */
interface EventInterface
{
    /**
     * List broadcast nsp array.
     */
    public static function broadcastOn(): array;

    /**
     * @param array $payload
     */
    public function setPayload($data): EventInterface;

    /**
     * Get event name.
     */
    public static function name(): string;
}
