<?php

namespace SfCod\SocketIoBundle\Command;

use SfCod\SocketIoBundle\Service\Worker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class WorkSocketIoCommand
 * Socketio worker. Use pm2 (http://pm2.keymetrics.io/) for fork command.
 *
 * @package yiicod\socketio\commands
 */
class PhpServerCommand extends Command
{
    /**
     * @var Worker
     */
    protected $worker;

    /**
     * PhpServerCommand constructor.
     *
     * @param Worker $worker
     */
    public function __construct(Worker $worker)
    {
        $this->worker = $worker;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('socket-io:php-server')
            ->setDescription('Work php server.');
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
        $io = new SymfonyStyle($input, $output);

        $io->success(sprintf('Worker daemon has been started.'));

        while (true) {
            $this->worker->predis();
        }
    }
}
