<?php

namespace SfCod\SocketIoBundle\Events;

/**
 * Class AbstractEvent
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package SfCod\SocketIoBundle\Events
 */
abstract class AbstractEvent implements EventInterface
{
    /**
     * Please, define '@property' and use it using magic __get() method instead of using $this->payload
     *
     * @var array
     */
    protected $payload;

    /**
     * @param $data
     *
     * @return EventInterface
     */
    public function setPayload($data): EventInterface
    {
        $this->payload = $data;

        return $this;
    }

    /**
     * Magic get
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->payload[$name])) {
            return $this->payload[$name];
        }

        return null;
    }
}
