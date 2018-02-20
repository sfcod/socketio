<?php

namespace SfCod\SocketIoBundle\Command;

use SfCod\SocketIoBundle\Base\WorkerTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class WorkSocketIoCommand
 * Socketio worker. Use pm2 (http://pm2.keymetrics.io/) for fork command.
 *
 * @package yiicod\socketio\commands
 */
class PhpServerCommand extends ContainerAwareCommand
{
    use WorkerTrait;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

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
        $this->input = $input;
        $this->output = $output;

        while (true) {
            $this->predis();
        }
    }

    public function output($text)
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $io->success($text);
    }
}
