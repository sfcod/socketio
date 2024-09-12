<?php

namespace SfCod\SocketIoBundle\Service;

use Exception;
use HTMLPurifier;
use Psr\Log\LoggerInterface;
use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\events\EventPolicyInterface;
use SfCod\SocketIoBundle\Events\EventPublisherInterface;
use SfCod\SocketIoBundle\events\EventRoomInterface;
use SfCod\SocketIoBundle\Events\EventSubscriberInterface;
use SfCod\SocketIoBundle\Exception\EventNotFoundException;
use SfCod\SocketIoBundle\Middleware\Process\ProcessMiddlewareInterface;

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
     * @var ProcessMiddlewareInterface[]
     */
    protected $processMiddlewares = [];
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

            foreach ($this->processMiddlewares as $processMiddleware) {
                if (false === $processMiddleware($handler, $data)) {
                    $this->logger->info(json_encode(['type' => 'process_terminated', 'name' => $handler, 'data' => $data]));

                    return;
                }
            }

            if (true === $eventHandler instanceof EventPolicyInterface && false === $eventHandler->can($data)) {
                $this->logger->info(json_encode(['type' => 'process_policy_forbidden', 'name' => $handler, 'data' => $data]));

                return;
            }

            $this->logger->info(json_encode(['type' => 'process', 'name' => $handler, 'data' => $data]));

            $eventHandler->handle();
        } catch (EventNotFoundException $e) {
            $this->logger->info($e);
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
        } catch (EventNotFoundException $e) {
            $this->logger->info($e);
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
        return $name . $_ENV['SOCKET_IO_NSP'];
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

    /**
     * @param ProcessMiddlewareInterface[] $processMiddlewares
     */
    public function setProcessMiddlewares($processMiddlewares)
    {
        $this->processMiddlewares = $processMiddlewares;
    }
}
