<?php

namespace SfCod\SocketIoBundle\Middleware\Process;

use Doctrine\ORM\EntityManagerInterface;

class DoctrineReconnect implements ProcessMiddlewareInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(string $handler, array $data): bool
    {
        $connection = $this->entityManager->getConnection();
        $connection->close();
        $connection->connect();

        return true;
    }
}
