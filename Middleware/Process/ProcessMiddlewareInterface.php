<?php

namespace SfCod\SocketIoBundle\Middleware\Process;

interface ProcessMiddlewareInterface
{
    public function __invoke(string $handler, array $data): bool;
}
