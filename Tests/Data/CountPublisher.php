<?php

namespace SfCod\SocketIoBundle\Tests\Data;

use SfCod\SocketIoBundle\Events\AbstractEvent;
use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\Events\EventPublisherInterface;

class CountPublisher extends AbstractEvent implements EventInterface, EventPublisherInterface
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
        return 'update_notification_count';
    }

    /**
     * Emit client event
     *
     * @return array
     */
    public function fire(): array
    {
        return [
            'count' => 10,
        ];
    }
}
