<?php

namespace PoolsPhp\Pools\Commands;

use PoolsPhp\Pools\RectorInstaller;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function array_filter;
use function explode;
use function var_dump;

#[AsCommand(name: 'install:rector')]
class InstallRectorCommand extends Command
{


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rectorIsInstalled = (new RectorInstaller)
            ->install($output);

        if(!$rectorIsInstalled) {
            return Command::FAILURE;
        }


        return Command::SUCCESS;
    }


}
