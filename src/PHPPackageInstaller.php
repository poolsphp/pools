<?php

declare(strict_types=1);

namespace Pools;

use Exception;
use Pools\Contracts\Installer;
use Pools\Contracts\PHPPackage;
use Pools\Exceptions\InstallationException;
use Pools\Exceptions\NoComposerException;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

use function array_keys;
use function assert;
use function property_exists;

final class PHPPackageInstaller implements Installer
{
    /**
     * @var array<string, PHPPackage>
     */
    private array $packagesToInstall = [];

    /**
     * @var array<string, PHPPackage>
     */
    private array $packagesToRemove = [];

    private readonly string $basePath;

    private readonly string $vendorPath;

    /**
     * @throws NoComposerException
     */
    public function __construct(
        private readonly ComposerManager $composerManager,
    ) {
        $this->vendorPath = $this->composerManager->getComposer()->getConfig()->get('vendor-dir');
        $this->basePath = dirname((string) $this->vendorPath);
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return array<string, PHPPackage>
     */
    public function getPackagesToInstall(): array
    {
        return $this->packagesToInstall;
    }

    public function getPackagesToRemove(): array
    {
        return $this->packagesToRemove;
    }

    public function getVendorPath(): string
    {
        return $this->vendorPath;
    }

    public function uninstall(OutputInterface|BufferedOutput $output): bool
    {

        if ($this->packagesToRemove === []) {
            return true;
        }

        return $this->handleUninstallProcess($output);

    }

    /**
     * @throws InstallationException|NoComposerException
     */
    public function install(OutputInterface|BufferedOutput $output): bool
    {

        if ($this->packagesToInstall === []) {
            return true;
        }

        return $this->handleInstallProcess($output);
    }

    public function removePackage(PHPPackage $package): Installer
    {
        assert(property_exists($package, 'package'));

        $this->packagesToRemove[$package->package] = $package;

        return $this;
    }

    public function requirePackage(PHPPackage $package): Installer
    {
        assert(property_exists($package, 'package'));

        $this->packagesToInstall[$package->package] = $package;

        return $this;
    }

    public function runPreInstallOperations(OutputInterface $output): void
    {
        foreach ($this->packagesToInstall as $package) {
            assert(property_exists($package, 'name'));
            $output->writeln("<info>Running pre-install operations for {$package->name}</info>");
            $package->beforeInstall();
        }
    }

    public function runPostInstallOperations(OutputInterface $output): void
    {
        foreach ($this->packagesToInstall as $package) {
            assert(property_exists($package, 'name'));
            $output->writeln("<info>Running post-install operations for {$package->name}</info>");
            $package->afterInstall();
        }
    }

    /**
     * @codeCoverageIgnore
     */
    private function handleUninstallProcess(OutputInterface|BufferedOutput $output): bool
    {
        $command = ['composer', 'remove', '--dev', '--no-interaction'];
        $packages = array_keys($this->packagesToRemove);
        $command = [...$command, ...$packages];

        $process = new Process($command);
        $process->setTimeout(null);

        try {

            $isAnyPackageInstalled = false;
            foreach ($packages as $package) {
                if ($this->composerManager->isPackageInstalled($package)) {
                    $isAnyPackageInstalled = true;
                    break;
                }
            }

            if (! $isAnyPackageInstalled) {
                return true;
            }

            $process->mustRun(function ($type, $buffer) use ($output): void {
                $output->write($buffer);
            });

            return true;

        } catch (Exception $e) {

            if (mb_stripos($e->getMessage(), 'depend') !== false) {
                $output->writeln('<error>Cannot remove packages due to dependencies.</error>');
                $output->writeln('<info>Try using --no-phpunit option when installing Pest.</info>');

                return false;
            }

        }

        return true;
    }

    /**
     * @codeCoverageIgnore
     *
     * @throws InstallationException
     */
    private function handleInstallProcess(OutputInterface|BufferedOutput $output): bool
    {
        $packagesToInstall = [];
        $packagesAlreadyInstalled = [];

        foreach (array_keys($this->packagesToInstall) as $package) {
            if ($this->composerManager->isPackageInstalled($package)) {
                $packagesAlreadyInstalled[] = $package;
            } else {
                $packagesToInstall[] = $package;
            }
        }

        if ($packagesAlreadyInstalled !== []) {
            $output->writeln('<info>The following packages are already installed:</info>');
            foreach ($packagesAlreadyInstalled as $package) {
                $output->writeln("  - {$package}");
            }
        }

        if ($packagesToInstall === []) {
            $output->writeln('<info>All requested packages are already installed.</info>');

            return true;
        }

        $command = ['composer', 'require', '--dev', '--no-interaction'];
        $command = [...$command, ...$packagesToInstall];

        $process = new Process($command);
        $process->setTimeout(null);

        try {
            $process->mustRun(function ($type, $buffer) use ($output): void {
                $output->write($buffer);
            });
        } catch (Exception $e) {
            throw new InstallationException('Failed to install packages: '.$e->getMessage());
        }

        return true;
    }
}
