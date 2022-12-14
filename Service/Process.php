<?php

namespace SfCod\SocketIoBundle\Service;

/**
 * Class Process.
 *
 * @package SfCod\SocketIoBundle
 */
class Process
{
    /**
     * @var array
     */
    private static $_inWork = [];

    /**
     * Run script name.
     *
     * @var string
     */
    private $scriptName;

    /**
     * Bin folder path.
     *
     * @var string
     */
    private $binPath;

    /**
     * Process constructor.
     */
    public function __construct(string $scriptName, string $binPath)
    {
        $this->scriptName = $scriptName;
        $this->binPath = $binPath;
    }

    /**
     * Get parallel processes count.
     */
    public function getParallelEnv(): int
    {
        return getenv('SOCKET_IO.PARALLEL') ? getenv('SOCKET_IO.PARALLEL') : 10;
    }

    /**
     * Run process. If more then limit then wait and try run process on more time.
     *
     * @return \Symfony\Component\Process\Process
     */
    public function run(string $handle, array $data)
    {
        $this->inWork();

        while (count(self::$_inWork) >= $this->getParallelEnv()) {
            usleep(100);

            $this->inWork();
        }

        return $this->push($handle, $data);
    }

    /**
     * In work processes.
     */
    private function inWork()
    {
        foreach (self::$_inWork as $i => $proccess) {
            /** @var \Symfony\Component\Process\Process $proccess * */
            if (false === $proccess->isRunning()) {
                unset(self::$_inWork[$i]);
            }
            // Client should not get any errors. But if get any errors they will be displayed on a display.
            if ($proccess->getErrorOutput()) {
                echo $proccess->getErrorOutput();
            }
        }
    }

    /**
     * Create cmd process and push to queue.
     */
    private function push(string $handle, array $data): \Symfony\Component\Process\Process
    {
        $cmd = sprintf('php %s socket-io:process --handler=%s --data=%s --env=%s', $this->scriptName, escapeshellarg($handle), escapeshellarg(serialize($data)), getenv('APP_ENV'));

        $process = \Symfony\Component\Process\Process::fromShellCommandline($cmd, $this->binPath);
        $process->setTimeout(10);
        $process->start();

        self::$_inWork[] = $process;

        return $process;
    }
}
