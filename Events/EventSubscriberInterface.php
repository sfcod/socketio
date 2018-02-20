<?php

namespace SfCod\SocketIoBundle\Events;

/**
 * Interface EventSubInterface
 * Event subscriber interface
 *
 * @package SfCod\SocketIoBundle\Events
 */
interface EventSubscriberInterface
{
    /**
     * Handle published event data
     *
     * @return void
     */
    public function handle();
}
