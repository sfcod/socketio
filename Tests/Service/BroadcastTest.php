<?php

namespace SfCod\SocketIoBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SfCod\SocketIoBundle\Events\EventInterface;
use SfCod\SocketIoBundle\Events\EventPublisherInterface;
use SfCod\SocketIoBundle\Events\EventSubscriberInterface;
use SfCod\SocketIoBundle\Service\Broadcast;
use SfCod\SocketIoBundle\Service\EventManager;
use SfCod\SocketIoBundle\Service\RedisDriver;
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
    private $eventManagerMock;
    private $processMock;

    /**
     * Set up test.
     */
    protected function setUp()
    {
        $this->configure();
        $redisDriverMock = $this->createMock(RedisDriver::class);
        $eventManagerMock = $this->createMock(EventManager::class);
        $this->eventManagerMock = $eventManagerMock;
        $loggerMock = $this->createMock(LoggerInterface::class);
        $processMock = $this->createMock(\SfCod\SocketIoBundle\Service\Process::class);
        $this->processMock = $processMock;

        $this->broadcast = new Broadcast($redisDriverMock, $eventManagerMock, $loggerMock, $processMock);
    }

    /**
     * Test on.
     *
     * @throws \Exception
     */
    public function testOn()
    {
        $this->processMock->method('run')->willReturn(new Process([]));
        $result = $this->broadcast->on(MarkAsReadSubscriber::name(), []);

        self::assertInstanceOf(Process::class, $result);
    }

    /**
     * Test process.
     */
    public function testProcess()
    {
        $data = range(1, 10);

        $handler = $this->getMockBuilder([EventInterface::class, EventSubscriberInterface::class])->getMock();
        $handler
            ->expects(self::once())
            ->method('setPayload')
            ->with($data);
        $handler
            ->expects(self::once())
            ->method('handle');

        $this->eventManagerMock->method('resolve')->willReturn($handler);
        $this->broadcast->process(get_class($handler), $data);
    }

    /**
     */
    public function testEmit()
    {
        $data = range(1, 10);

        $handler = $this->getMockBuilder([EventInterface::class, EventPublisherInterface::class])->getMock();
        $handler
            ->expects(self::once())
            ->method('setPayload')
            ->with($data);
        $handler
            ->expects(self::once())
            ->method('fire');
        $this->eventManagerMock->method('resolve')->willReturn($handler);
        $this->broadcast->emit(get_class($handler), $data);
    }

//    /**
//     */
//    public function testChannels()
//    {
//
//    }
}
