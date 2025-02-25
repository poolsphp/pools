<?php

namespace PoolsPhp\Pools;

use Symfony\Component\Process\Process;

final readonly class RectorInstaller implements Contracts\PackageInstaller
{
    public function install(): bool
    {

        $output = [];

        $process = new Process(['composer', 'require', 'rector/rector', '--dev']);
        $process->setTimeout(null);

        try {
            $process->mustRun(function ($type, $buffer) use (&$output): void {
                $output[] = $buffer;
            });
        } catch (\Exception) {
            return false;
        }

        return true;

    }
}
