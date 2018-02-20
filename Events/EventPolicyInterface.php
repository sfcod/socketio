<?php

namespace SfCod\SocketIoBundle\Events;

/**
 * Interface EventPolicyInterface
 *
 * @package SfCod\SocketIoBundle\Events
 */
interface EventPolicyInterface
{
    /**
     * Can event be processed
     *
     * @param $data
     *
     * @return bool
     */
    public function can($data): bool;
}
