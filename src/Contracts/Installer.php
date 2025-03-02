<?php

declare(strict_types=1);

namespace PoolsPhp\Pools\Contracts;

use Symfony\Component\Console\Output\OutputInterface;

interface Installer
{
    /**
     * Add a package to the installation list
     */
    public function requirePackage(PHPPackage $package): self;

    /**
     * Add a package to the removal list
     */
    public function removePackage(PHPPackage $package): self;

    /**
     * Install packages
     */
    public function install(OutputInterface $output): bool;

    /**
     * Uninstall packages
     */
    public function uninstall(OutputInterface $output): bool;

    /**
     * Run post-installation operations
     */
    public function runPostInstallOperations(OutputInterface $output): void;

    /**
     * Run pre-installation operations
     */
    public function runPreInstallOperations(OutputInterface $output): void;
}
