<?php

namespace SfCod\SocketIoBundle\Exception;

use InvalidArgumentException;

/**
 * Class EventNotFoundException
 *
 * @author Orlov Alexey <aaorlov88@gmail.com>
 *
 * @package SfCod\SocketIoBundle\Exception
 */
class EventNotFoundException extends InvalidArgumentException
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     *
     * @return void
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
