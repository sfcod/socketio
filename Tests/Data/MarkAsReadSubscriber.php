<?php

namespace SfCod\SocketIoBundle\Tests\Data;

use SfCod\SocketIoBundle\Events\AbstractEvent;
use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\Events\EventSubscriberInterface;
use SfCod\SocketIoBundle\Service\Broadcast;

class MarkAsReadSubscriber extends AbstractEvent implements EventInterface, EventSubscriberInterface
{
    /**
     * Changel name. For client side this is nsp.
     */
    public static function broadcastOn(): array
    {
        return ['notifications'];
    }

    /**
     * Event name
     */
    public static function name(): string
    {
        return 'mark_as_read_notification';
    }

    /**
     * Emit client event
     */
    public function handle()
    {
        // Mark notification as read
        // And call client update
        $this->container->get(Broadcast::class)->emit('update_notification_count', ['some key' => 'some value']);
    }
}
