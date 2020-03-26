<?php

namespace SfCod\SocketIoBundle\Command;

use SfCod\SocketIoBundle\Service\Worker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class WorkSocketIoCommand
 * Socketio worker. Use pm2 (http://pm2.keymetrics.io/) for fork command.
 *
 * @package yiicod\socketio\commands
 */
class NodeJsServerCommand extends Command
{
    /**
     * @var Worker
     */
    protected $worker;

    /**
     * NodeJsServerCommand constructor.
     */
    public function __construct(Worker $worker)
    {
        $this->worker = $worker;

        parent::__construct();
    }

    /**
     * Configure command.
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
     * Execute command.
     *
     * @return int|void|null
     *
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->success(sprintf('Worker daemon has been started.'));

        $process = $this->worker->nodeJs(
            $input->getOption('server') ?? getenv('SOCKET_IO_WS_SERVER'),
            $input->getOption('ssl') ?? getenv('SOCKET_IO_SSL') ? getenv('SOCKET_IO_SSL') : ''
        );
        $process->setIdleTimeout(false);
        $process->setTimeout(null);
        $process->run();
    }
}
