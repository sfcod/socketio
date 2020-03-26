<?php

namespace SfCod\SocketIoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use HTMLPurifier;
use Psr\Log\LoggerInterface;
use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\events\EventPolicyInterface;
use SfCod\SocketIoBundle\Events\EventPublisherInterface;
use SfCod\SocketIoBundle\events\EventRoomInterface;
use SfCod\SocketIoBundle\Events\EventSubscriberInterface;

/**
 * Class Broadcast.
 *
 * @package SfCod\SocketIoBundle
 */
class Broadcast
{
    /**
     * @var array
     */
    protected static $channels = [];
    /**
     * @var RedisDriver
     */
    protected $redis;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var EventManager
     */
    protected $manager;
    /**
     * @var Process
     */
    protected $process;
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Broadcast constructor.
     */
    public function __construct(RedisDriver $redis, EventManager $manager, LoggerInterface $logger, Process $process)
    {
        $this->redis = $redis;
        $this->logger = $logger;
        $this->manager = $manager;
        $this->process = $process;
    }

    /**
     * Subscribe to event from client.
     *
     * @return \Symfony\Component\Process\Process
     *
     * @throws Exception
     */
    public function on(string $event, array $data)
    {
        // Clear data
        array_walk_recursive($data, function (&$item, $key) {
            $item = (new HtmlPurifier())->purify($item);
        });

        $this->logger->info(json_encode(['type' => 'on', 'name' => $event, 'data' => $data]));

        return $this->process->run($event, $data);
    }

    /**
     * Run process.
     */
    public function process(string $handler, array $data)
    {
        try {
            /** @var EventInterface|EventSubscriberInterface|EventPolicyInterface $eventHandler */
            $eventHandler = $this->manager->resolve($handler); //container->get(sprintf('socketio.%s', $handler));

            if (false === $eventHandler instanceof EventInterface) {
                throw new Exception('Event should implement EventInterface');
            }

            $eventHandler->setPayload($data);

            if (false === $eventHandler instanceof EventSubscriberInterface) {
                throw new Exception('Event should implement EventSubscriberInterface');
            }

            if (true === $eventHandler instanceof EventPolicyInterface && false === $eventHandler->can($data)) {
                return;
            }

            if ($this->entityManager) {
                $connection = $this->entityManager->getConnection();
                $connection->close();
                $connection->connect();
            }

            $this->logger->info(json_encode(['type' => 'process', 'name' => $handler, 'data' => $data]));

            $eventHandler->handle();
        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * Emit event to client.
     *
     * @throws Exception
     */
    public function emit(string $event, array $data)
    {
        $this->logger->info(json_encode(['type' => 'emit', 'name' => $event, 'data' => $data]));

        try {
            /** @var EventInterface|EventPublisherInterface|EventRoomInterface $eventHandler */
            $eventHandler = $this->manager->resolve($event); // container->get(sprintf('socketio.%s', $event));

            if (false === $eventHandler instanceof EventInterface) {
                throw new Exception('Event should implement EventInterface');
            }

            $eventHandler->setPayload($data);

            if (false === $eventHandler instanceof EventPublisherInterface) {
                throw new Exception('Event should implement EventPublisherInterface');
            }

            $data = $eventHandler->fire();

            if ($eventHandler instanceof EventRoomInterface) {
                $data['room'] = $eventHandler->room();
            }

            $eventHandlerClass = get_class($eventHandler);
            foreach ($eventHandlerClass::broadcastOn() as $channel) {
                $this->publish($this->channelName($channel), [
                    'name' => $eventHandlerClass::name(),
                    'data' => $data,
                ]);
            }
        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * Publish data to redis channel.
     */
    protected function publish(string $channel, array $data)
    {
        $this->redis->getClient(true)->publish($channel, json_encode($data));
    }

    /**
     * Prepare channel name.
     */
    protected function channelName(string $name): string
    {
        return $name . getenv('SOCKET_IO_NSP');
    }

    /**
     * Redis channels names.
     */
    public function channels(): array
    {
        if (empty(self::$channels)) {
            foreach ($this->manager->getList() as $eventHandlerClass) {
                self::$channels = array_merge(self::$channels, $eventHandlerClass::broadcastOn());
            }
            self::$channels = array_unique(self::$channels);
            self::$channels = array_map(function ($channel) {
                return $this->channelName($channel);
            }, self::$channels);
        }

        return self::$channels;
    }

    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
