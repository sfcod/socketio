<?php

namespace SfCod\SocketIoBundle\Service;

use Predis\Connection\ConnectionException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Class Worker.
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\SocketIoBundle\Service
 */
class Worker
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var RedisDriver
     */
    private $redisDriver;

    /**
     * @var Broadcast
     */
    private $broadcast;

    /**
     * @var string
     */
    private $logDir;

    /**
     * Worker constructor.
     */
    public function __construct(EventManager $eventManager, RedisDriver $redisDriver, Broadcast $broadcast, string $logDir)
    {
        $this->eventManager = $eventManager;
        $this->redisDriver = $redisDriver;
        $this->broadcast = $broadcast;
        $this->logDir = $logDir;
    }

    /**
     * Get node js process.
     */
    public function nodeJs(string $server, string $ssl = ''): Process
    {
        $cmd = sprintf('node %s/%s', dirname(__DIR__ . '../Server'), 'index.js');

        $connection = json_encode(array_filter([
            'host' => $this->redisDriver->getHost(),
            'port' => $this->redisDriver->getPort(),
            'password' => $this->redisDriver->getPassword(),
        ]));

        $args = array_filter([
            'server' => $server,
            'pub' => $connection,
            'sub' => $connection,
            'channels' => implode(',', $this->broadcast->channels()),
            'nsp' => getenv('SOCKET_IO_NSP'),
            'ssl' => empty($ssl) ? null : $ssl,
            'runtime' => $this->logDir,
        ], 'strlen');
        foreach ($args as $key => $value) {
            $cmd .= ' -' . $key . '=\'' . $value . '\'';
        }

        return new Process([$cmd]);
    }

    /**
     * Start predis.
     */
    public function predis(SymfonyStyle $io)
    {
        $pubSubLoop = function () use ($io) {
            /** @var \Predis\Client $client */
            $client = $this->redisDriver->getClient(true);

            // Initialize a new pubsub consumer.
            $pubSub = $client->pubSubLoop();

            $channels = [];
            foreach ($this->broadcast->channels() as $key => $channel) {
                $channels[$key] = $channel . '.io';
            }

            // Subscribe to your channels
            $pubSub->subscribe(array_merge(['control_channel'], $channels));

            // Start processing the pubsup messages. Open a terminal and use redis-cli
            // to push messages to the channels. Examples:
            //   ./redis-cli PUBLISH notifications "this is a test"
            //   ./redis-cli PUBLISH control_channel quit_loop
            foreach ($pubSub as $message) {
                switch ($message->kind) {
                    case 'subscribe':
                        $io->success("Subscribed to {$message->channel}");
                        break;
                    case 'message':
                        if ('control_channel' == $message->channel) {
                            if ('quit_loop' == $message->payload) {
                                $io->success("Aborting pubsub loop...\n");
                                $pubSub->unsubscribe();
                            } else {
                                $io->success("Received an unrecognized command: {$message->payload}\n");
                            }
                        } else {
                            $payload = json_decode($message->payload, true);
                            $data = $payload['data'] ?? [];

                            $this->broadcast->on($payload['name'], $data);
                        }
                        break;
                }
            }

            // Always unset the pubsub consumer instance when you are done! The
            // class destructor will take care of cleanups and prevent protocol
            // desynchronizations between the client and the server.
            unset($pubSub);
        };

        // Auto recconnect on redis timeout
        try {
            $pubSubLoop();
        } catch (ConnectionException $e) {
            $pubSubLoop();
        }
    }
}
