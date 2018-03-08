<?php

namespace SfCod\SocketIoBundle\Base;

use SfCod\SocketIoBundle\Service\Broadcast;
use SfCod\SocketIoBundle\Service\EventManager;
use SfCod\SocketIoBundle\Service\RedisDriver;
use Symfony\Component\Process\Process;

trait WorkerTrait
{
    /**
     * @return mixed
     */
    public function getEventManager()
    {
        return $this->eventManager ?? $this->getContainer()->get(EventManager::class);
    }

    /**
     * Get node js process
     *
     * @param string $server
     * @param array $ssl
     *
     * @return Process
     */
    public function nodeJs(string $server, string $ssl = ''): Process
    {
        $cmd = sprintf('node %s/%s', realpath(dirname(__FILE__) . '/../Server'), 'index.js');

        /** @var RedisDriver $redis */
        $redis = $this->getContainer()->get(RedisDriver::class);
        $connection = json_encode(array_filter([
            'host' => $redis->getHost(),
            'port' => $redis->getPort(),
            'password' => $redis->getPassword(),
        ]));

        $args = array_filter([
            'server' => $server,
            'pub' => $connection,
            'sub' => $connection,
            'channels' => implode(',', $this->getContainer()->get(Broadcast::class)->channels()),
            'nsp' => getenv('SOCKET_IO_NSP'),
            'ssl' => empty($ssl) ? null : $ssl,
            'runtime' => $this->getContainer()->get('kernel')->getRootDir() . '/../../var/log/socketio',
        ], 'strlen');
        foreach ($args as $key => $value) {
            $cmd .= ' -' . $key . '=\'' . $value . '\'';
        }

        $process = new Process($cmd);

        return $process;
    }

    /**
     * Start predis
     */
    public function predis()
    {
        $pubSubLoop = function () {
            /** @var \Predis\Client $client */
            $client = $this->getContainer()->get(RedisDriver::class)->getClient(true);

            // Initialize a new pubsub consumer.
            $pubSub = $client->pubSubLoop();

            $channels = [];
            foreach ($this->getContainer()->get(Broadcast::class)->channels() as $key => $channel) {
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
                        $this->output("Subscribed to {$message->channel}");
                        break;
                    case 'message':
                        if ('control_channel' == $message->channel) {
                            if ('quit_loop' == $message->payload) {
                                $this->output("Aborting pubsub loop...\n");
                                $pubSub->unsubscribe();
                            } else {
                                $this->output("Received an unrecognized command: {$message->payload}\n");
                            }
                        } else {
                            $payload = json_decode($message->payload, true);
                            $data = $payload['data'] ?? [];

                            $this->getContainer()->get(Broadcast::class)->on($payload['name'], $data);
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
        } catch (\Predis\Connection\ConnectionException $e) {
            $pubSubLoop();
        }
    }
}
