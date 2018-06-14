<?php

namespace SfCod\SocketIoBundle\Service;

use LogicException;
use Predis\Client;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * Class RedisDriver
 *
 * @package SfCod\SocketIoBundle\drivers
 */
class RedisDriver
{
    /**
     * @var string
     */
    protected $dsn;

    /**
     * @var
     */
    protected $client;

    /**
     * RedisDriver constructor.
     *
     * @param string $dsn
     */
    public function __construct(string $dsn = 'redis://localhost:6379')
    {
        $this->dsn = $dsn;
    }

    /**
     * Get redis client
     *
     * @param bool $reset
     *
     * @return Client
     */
    public function getClient(bool $reset = false): Client
    {
        if (is_null($this->client) || $reset) {
            $client = RedisAdapter::createConnection($this->dsn);

            if ($client instanceof Client) {
                $this->client = $client;
            } else {
                throw new LogicException('Unsupported redis client.');
            }
        }

        return $this->client;
    }
}
