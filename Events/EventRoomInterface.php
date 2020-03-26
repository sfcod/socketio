<?php

namespace SfCod\SocketIoBundle\Events;

/**
 * Interface EventRoomInterface
 * Provide room support for event.
 *
 * @package SfCod\SocketIoBundle\Events
 */
interface EventRoomInterface
{
    /**
     * Get room name.
     */
    public function room(): string;
}
