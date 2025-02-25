<?php

namespace PoolsPhp\Pools;

use PoolsPhp\Pools\Contracts\PackageInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

final readonly class RectorInstaller implements Contracts\PackageInstaller
{



    public function install(OutputInterface $output): bool
    {

        $output->writeln('<info>Installing Rector...</info>');

        $process = new Process(['composer', 'require', 'rector/rector', '--dev']);
        $process->setTimeout(null);

        try {
            $process->mustRun(function ($type, $buffer) use ($output) {
                $output->write("<info>$buffer</info>");
            });
        } catch (\Exception $e) {
            $output->writeln("<error>Failed to install Rector</error>");
            return false;
        }

        $output->writeln("<info>Rector installed successfully</info>");
        return true;




    }
}
