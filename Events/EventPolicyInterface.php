<?php

namespace SfCod\SocketIoBundle\Events;

/**
 * Interface EventPolicyInterface.
 *
 * @package SfCod\SocketIoBundle\Events
 */
interface EventPolicyInterface
{
    /**
     * Can event be processed.
     *
     * @param $data
     */
    public function can($data): bool;
}
