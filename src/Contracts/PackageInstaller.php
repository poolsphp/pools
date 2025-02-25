<?php

declare(strict_types= 1);

namespace PoolsPhp\Pools\Contracts;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal description
 */
interface PackageInstaller
{
    public function install(OutputInterface $output): bool;

}
