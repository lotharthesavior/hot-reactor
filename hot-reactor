#!/usr/bin/env php

<?php

use Dotenv\Dotenv;
use HotReactor\Commands\HotReactorCommand;
use OpenSwoole\Coroutine\System;
use OpenSwoole\Runtime;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

Co::set(['hook_flags'=> Runtime::HOOK_ALL]);

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$application = new Application();

$command = new HotReactorCommand();
$application->add($command);
$application->setDefaultCommand($command->getName(), true);
$application->run();

Co::run(fn() => System::waitSignal(SIGKILL, -1));
