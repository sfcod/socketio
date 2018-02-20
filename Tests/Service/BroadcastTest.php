<?php

namespace SfCod\SocketIoBundle\Tests;

use PHPUnit\Framework\TestCase;
use SfCod\SocketIoBundle\Service\Broadcast;
use SfCod\SocketIoBundle\Tests\Data\LoadTrait;
use SfCod\SocketIoBundle\Tests\Data\MarkAsReadSubscriber;

class BroadcastTest extends TestCase
{
    use LoadTrait;

    /**
     * @var Broadcast
     */
    private $broadcast;

    protected function setUp()
    {
        $this->configure();

        $this->broadcast = $this->container->get(Broadcast::class);
    }

    public function testOn()
    {
        $result = $this->broadcast->on(MarkAsReadSubscriber::name(), []);

        $this->assertTrue(is_a($result, \Symfony\Component\Process\Process::class));
    }

    public function testProcess()
    {
    }

    public function testEmit()
    {
    }

    public function testChannels()
    {
    }
}
