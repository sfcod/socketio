<?php

namespace SfCod\SocketIoBundle\Command;

use Psr\Log\LoggerInterface;
use SfCod\SocketIoBundle\Service\Broadcast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProcessCommand
 * Run this daemon for listen socketio. Don't forget about run npm install in the folder "server".
 *
 * @package yiicod\socketio\commands
 */
class ProcessCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Broadcast
     */
    protected $broadcast;

    /**
     * ProcessCommand constructor.
     *
     * @param Broadcast $broadcast
     */
    public function __construct(Broadcast $broadcast)
    {
        $this->broadcast = $broadcast;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('socket-io:process')
            ->setDescription('Starts socket-io process.')
            ->addOption('handler', null, InputArgument::OPTIONAL, 'Process handler class', null)
            ->addOption('data', null, InputArgument::OPTIONAL, 'Serialized handle data.', null);
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $handler = $input->getOption('handler');

        $this->broadcast->process($handler, @unserialize($input->getOption('data')) ?? []);
    }
}
