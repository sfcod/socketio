<?php

namespace SfCod\SocketIoBundle\Tests;

use PHPUnit\Framework\TestCase;
use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\Events\EventPublisherInterface;
use SfCod\SocketIoBundle\Events\EventSubscriberInterface;
use SfCod\SocketIoBundle\Service\Broadcast;
use SfCod\SocketIoBundle\Tests\Data\LoadTrait;
use SfCod\SocketIoBundle\Tests\Data\MarkAsReadSubscriber;
use Symfony\Component\Process\Process;

/**
 * Class BroadcastTest.
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\SocketIoBundle\Tests
 */
class BroadcastTest extends TestCase
{
    use LoadTrait;

    /**
     * @var Broadcast
     */
    private $broadcast;

    /**
     * Set up test.
     */
    protected function setUp()
    {
        $this->configure();

        $this->broadcast = $this->container->get(Broadcast::class);
    }

    /**
     * Test on.
     *
     * @throws \Exception
     */
    public function testOn()
    {
        $result = $this->broadcast->on(MarkAsReadSubscriber::name(), []);

        $this->assertInstanceOf(Process::class, $result);
    }

    /**
     * Test process.
     */
    public function testProcess()
    {
        $data = range(1, 10);

        $handler = $this->getMockBuilder([EventInterface::class, EventSubscriberInterface::class])->getMock();
        $handler
            ->expects($this->once())
            ->method('setPayload')
            ->with($data);
        $handler
            ->expects($this->once())
            ->method('handle');

        $this->container->set(sprintf('socketio.%s', get_class($handler)), $handler);
        $this->broadcast->process(get_class($handler), $data);
    }

//    /**
//     */
//    public function testEmit()
//    {
//        $data = range(1, 10);
//
//        $handler = $this->getMockBuilder([EventInterface::class, EventPublisherInterface::class])->getMock();
//        $handler
//            ->expects($this->once())
//            ->method('setPayload')
//            ->with($data);
//        $handler
//            ->expects($this->once())
//            ->method('fire');
//
//        $this->container->set(sprintf('socketio.%s', get_class($handler)), $handler);
//        $this->broadcast->emit(get_class($handler), $data);
//    }

//    /**
//     */
//    public function testChannels()
//    {
//
//    }
}
