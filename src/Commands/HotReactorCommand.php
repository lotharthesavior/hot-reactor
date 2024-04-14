<?php

namespace HotReactor\Commands;

use HotReactor\HotReactor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'jackit:run')]
class HotReactorCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                name: 'working-dir',
                shortcut: 'w',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Current working directory. e.g: -w "/var/www"',
                default: $_ENV['WORKING_DIR'] ?? null,
            )
            ->addOption(
                name: 'command',
                shortcut: 'c',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Command to be execute. e.g: -c "php server.php"',
                default: $_ENV['COMMAND'] ?? null,
            )
            ->addOption(
                name: 'objects',
                shortcut: 'o',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Files or folders to watch, separated by pipe (|). e.g: -o "./src|./resources"',
                default: $_ENV['OBJECTS'] ?? null,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workingDir = $input->getOption('working-dir');
        $command = $input->getOption('command');
        $objects = $input->getOption('objects');

        if (null === $command) {
            $output->writeln('Command option is required.');
            return Command::FAILURE;
        }

        if (null === $objects) {
            $output->writeln('Objects option is required.');
            return Command::FAILURE;
        }

        if (null !== $workingDir && !chdir($workingDir)) {
            $output->writeln('Working directory does not exist.');
            return Command::FAILURE;
        }

        new HotReactor($command, $objects, $workingDir);

        return Command::SUCCESS;
    }
}
