<?php

namespace SfCod\SocketIoBundle\Service;

use Psr\Log\LoggerInterface;
use SfCod\SocketIoBundle\Events\AbstractEvent;
use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\Events\EventPublisherInterface;
use SfCod\SocketIoBundle\Events\EventSubscriberInterface;

class JoinHandler extends AbstractEvent implements EventInterface, EventSubscriberInterface, EventPublisherInterface
{
    private $broadcast;
    private $logger;

    public function __construct(Broadcast $broadcast, LoggerInterface $logger)
    {
        $this->broadcast = $broadcast;
        $this->logger = $logger;
    }

    /**
     * Changel name. For client side this is nsp.
     */
    public static function broadcastOn(): array
    {
        return [''];
    }

    public function fire(): array
    {
        $this->logger->info(json_encode(['type' => 'fire', 'name' => self::name(), 'data' => $this->payload]));

        return [
            'room' => $this->payload['room'],
            'socketId' => $this->payload['socketId'],
        ];
    }

    /**
     * Event name
     */
    public static function name(): string
    {
        return 'join';
    }

    /**
     * Handle client event
     */
    public function handle()
    {
        $this->logger->info(json_encode(['type' => 'handle', 'name' => self::name(), 'data' => $this->payload]));
        $this->broadcast->emit(self::name(), [
            'room' => $this->payload['room'],
            'socketId' => $this->payload['socketId'],
        ]);
    }
}
