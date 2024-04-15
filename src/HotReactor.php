<?php

namespace HotReactor;

use OpenSwoole\Atomic;
use OpenSwoole\Coroutine as Co;
use OpenSwoole\Coroutine\System;
use OpenSwoole\Event;
use OpenSwoole\Process;
use Symfony\Component\Process\Process as SymfonyProcess;
use OpenSwoole\Util;

class HotReactor
{
    protected Atomic $atomic;
    protected ?int $commandPid = null;

    public function __construct(
        protected string $command,
        protected string $objects,
        protected ?string $workingDir,
    ) {
        Util::setProcessName($_ENV['MAIN_PROCESS_NAME']);

        $this->atomic = new Atomic(0);

        (new Process(fn() => Co::run(fn() => $this->startService())))->start();

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
    private function startService(): void
    {
        $pid = $this->getServicePidByName($_ENV['OBJECT_PROCESS_NAME']);

        if ($pid !== null) {
            echo 'Killing service process...' . PHP_EOL;
            Process::kill($pid, SIGKILL);
        }

        if ($this->commandPid) {
            Process::kill($this->commandPid, SIGKILL);
        }

        echo 'Starting server...' . PHP_EOL;

        go(function () {
            $process = new SymfonyProcess(preg_split('/\s+/', $this->command));
            $process->setTimeout(null);
            $process->start();
            $this->commandPid = $process->getPid();

            foreach ($process as $type => $data) {
                if ($process::OUT === $type) {
                    echo 'OUT: ' . $data . PHP_EOL;
                } else {
                    echo 'ERR: ' . $data . PHP_EOL;
                }
            }
        });
    }

    /**
     * Retrieve the service PID by name.
     *
     * @param string $name
     * @return int|null
     */
    private function getServicePidByName(string $name): ?int
    {
        $pid = System::exec(
            command: '/usr/bin/ps -aux'
            . ' | grep ' . $name
            . ' | grep -v \'grep ' . $name . '\''
            . ' | /usr/bin/awk \'{ print $2; }\''
            . ' | /usr/bin/sed -n \'1,1p\''
        );
        $cleanPid = trim($pid['output'] ?? '');

        return (int) $cleanPid ?: null;
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
            if (!preg_match('/\.php$/', $filePath) || preg_match('/\.php~$/', $filePath)) {
                continue;
            }
            $this->startService();
            echo 'File ' . $filePath . ' has been reloaded.' . PHP_EOL;
            break;
        }
        sleep(1);

        $this->atomic->set(0);
    }
}
