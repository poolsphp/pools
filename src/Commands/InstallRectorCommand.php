<?php

namespace PoolsPhp\Pools\Commands;

use PoolsPhp\Pools\RectorInstaller;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'install:rector')]
class InstallRectorCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Installing Rector...</info>');
        $rectorIsInstalled = (new RectorInstaller)
            ->install();

        if (! $rectorIsInstalled) {
            $output->writeln('<error>Failed to install Rector</error>');

            return Command::FAILURE;
        }
        $output->writeln('<info>Rector installed successfully</info>');

        return Command::SUCCESS;
    }
}
