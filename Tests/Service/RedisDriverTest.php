<?php

namespace SfCod\SocketIoBundle\Tests;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use SfCod\SocketIoBundle\Service\RedisDriver;

/**
 * Class RedisDriverTest
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\SocketIoBundle\Tests
 */
class RedisDriverTest extends TestCase
{
    /**
     * Test redis client
     */
    public function testGetClient()
    {
        $driver = new RedisDriver('redis://localhost:6379');

        $this->assertInstanceOf(Client::class, $driver->getClient());
    }
}
