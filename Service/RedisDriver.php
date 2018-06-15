<?php

namespace SfCod\SocketIoBundle\Service;

use Predis\Client;

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
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var string
     */
    protected $socket;

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
        $this->parseDsn($dsn);
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
            $this->client = new Client([
                'scheme' => 'tcp',
                'read_write_timeout' => 0,
                'host' => $this->host,
                'port' => $this->port,
                'database' => $this->database,
                'password' => $this->password,
            ]);
        }

        return $this->client;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getDatabase(): ?string
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getSocket(): ?string
    {
        return $this->socket;
    }

    /**
     * Parse dsn and get all needed attributes
     *
     * @param string $dsn
     */
    protected function parseDsn($dsn)
    {
        $dsn = str_replace('redis://', '', $dsn); // remove "redis://"
        if (false !== $pos = strrpos($dsn, '@')) {
            // parse password
            $password = substr($dsn, 0, $pos);
            if (strstr($password, ':')) {
                list(, $password) = explode(':', $password, 2);
            }
            $this->password = urldecode($password);
            $dsn = substr($dsn, $pos + 1);
        }
        $dsn = preg_replace_callback('/\?(.*)$/', [$this, 'parseParameters'], $dsn); // parse parameters
        if (preg_match('#^(.*)/(\d+|%[^%]+%|env_\w+_[[:xdigit:]]{32,})$#', $dsn, $matches)) {
            // parse database
            $this->database = is_numeric($matches[2]) ? (int)$matches[2] : $matches[2];
            $dsn = $matches[1];
        }
        if (preg_match('#^([^:]+)(:(\d+|%[^%]+%|env_\w+_[[:xdigit:]]{32,}))?$#', $dsn, $matches)) {
            if (!empty($matches[1])) {
                // parse host/ip or socket
                if ('/' === $matches[1][0]) {
                    $this->socket = $matches[1];
                } else {
                    $this->host = $matches[1];
                }
            }
            if (null === $this->socket && !empty($matches[3])) {
                // parse port
                $this->port = is_numeric($matches[3]) ? (int)$matches[3] : $matches[3];
            }
        }
    }

    /**
     * @param array $matches
     *
     * @return string
     */
    protected function parseParameters($matches)
    {
        parse_str($matches[1], $params);
        foreach ($params as $key => $val) {
            if (!$val) {
                continue;
            }
//            switch ($key) {
//                case 'weight':
//                    $this->weight = (int)$val;
//                    break;
//                case 'alias':
//                    $this->alias = $val;
//                    break;
//            }
        }

        return '';
    }
}
