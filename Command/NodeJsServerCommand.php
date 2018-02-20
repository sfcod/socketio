<?php

namespace SfCod\SocketIoBundle\Command;

use SfCod\SocketIoBundle\Base\WorkerTrait;
use SfCod\SocketIoBundle\Service\EventManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WorkSocketIoCommand
 * Socketio worker. Use pm2 (http://pm2.keymetrics.io/) for fork command.
 *
 * @package yiicod\socketio\commands
 */
class NodeJsServerCommand extends ContainerAwareCommand
{
    use WorkerTrait;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * NodeJsServerCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('socket-io:node-js-server')
            ->setDescription('Work node-js server.')
            ->addOption('server', null, InputArgument::OPTIONAL, 'SocketIo server.', null)
            ->addOption('ssl', null, InputArgument::OPTIONAL, 'SSL certificate config.', null);
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $process = $this->nodeJs(
            $input->getOption('server') ?? getenv('SOCKET_IO_WS_SERVER'),
            $input->getOption('ssl') ?? getenv('SOCKET_IO_SSL') ? (array)getenv('SOCKET_IO_SSL') : []
        );
        $process->setIdleTimeout(false);
        $process->setTimeout(null);
        $process->run();
    }
}
