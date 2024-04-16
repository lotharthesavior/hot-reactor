<?php

namespace HotReactor;

use OpenSwoole\Atomic;
use OpenSwoole\Coroutine as Co;
use OpenSwoole\Event;
use OpenSwoole\Process;
use OpenSwoole\Util;

class HotReactor
{
    protected Process $process;
    protected int $processPid;

    protected Process $subProcess;
    protected int $subProcessPid;

    protected Atomic $atomic;

    public function __construct(
        protected string $command,
        protected string $objects,
        protected ?string $workingDir,
    ) {
        Util::setProcessName($_ENV['MAIN_PROCESS_NAME']);

        $this->atomic = new Atomic(0);

        $this->process = new Process(function (Process $worker) {
            $this->subProcess = new Process(fn (Process $sub) => $this->startService($sub));
            $this->subProcessPid = $this->subProcess->start();

            while ($data = $worker->read()) {
                Process::kill($this->subProcessPid, SIGKILL);
                $this->subProcess = new Process(fn (Process $sub) => $this->startService($sub));
                $this->subProcessPid = $this->subProcess->start();
                echo 'File ' . $data . ' has been reloaded.' . PHP_EOL;
            }
        }, false);
        $this->processPid = $this->process->start();

        $this->startInotify();

        Event::wait();
    }

    /**
     * Start inotify.
     *
     * @return void
     */
    private function startInotify(): void
    {
        echo 'Starting inotify...' . PHP_EOL;

        $workDir = $this->workingDir ?? __DIR__ . '/';
        $inotify = inotify_init();
        foreach (explode('|', $this->objects) as $object) {
            inotify_add_watch($inotify, $object, IN_MODIFY | IN_CREATE | IN_MOVE | IN_DELETE);
        }

        Event::add($inotify, fn() => $this->inotifyCallback($inotify, $workDir));
    }

    /**
     * Start the Service.
     *
     * @return void
     */
    private function startService(Process $worker): void
    {
        Co::run(function () use ($worker) {
            $command = preg_split('/\s+/', $this->command);
            $commandFile = $command[0];
            unset($command[0]);

            echo 'Restarting service...' . PHP_EOL;
            echo PHP_EOL . '================ OUT ================' . PHP_EOL;
            $worker->exec($commandFile, $command);
        });
    }

    /**
     * Inotify callback.
     *
     * @param resource $inotify
     * @param string $workDir
     * @return void
     */
    private function inotifyCallback($inotify, string $workDir): void
    {
        if ($this->atomic->get() > 0){
            return;
        }

        $events = @inotify_read($inotify);

        echo PHP_EOL . PHP_EOL . 'Event...' . PHP_EOL;
        if (!$events){
            return;
        }
        $this->atomic->add();

        foreach ($events as $event) {
            $filePath = $workDir . $event['name'];
            if (!preg_match('/\.(' . $_ENV['FILE_EXTENSIONS'] . ')$/', $filePath) || preg_match('/\.php~$/', $filePath)) {
                continue;
            }
            $this->process->write($filePath);
            break;
        }
        sleep(1);

        $this->atomic->set(0);
    }
}
