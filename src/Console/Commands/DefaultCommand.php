<?php

declare(strict_types=1);

namespace Pools\Console\Commands;

use Exception;
use Pools\ComposerManager;
use Pools\Exceptions\InstallationException;
use Pools\Exceptions\NoComposerException;
use Pools\Packages\PestPackage;
use Pools\Packages\PhpStanPackage;
use Pools\Packages\PHPUnitPackage;
use Pools\Packages\PintPackage;
use Pools\Packages\RectorPackage;
use Pools\PHPPackageInstaller;
use Pools\ValueObjects\PackagePayload;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'default')]
final class DefaultCommand extends Command
{
    protected function configure(): void
    {

        $this
            ->setDescription('Installs modern PHP tools in your project')
            ->addOption(
                'no-phpunit',
                null,
                InputOption::VALUE_NONE,
                'Do not remove PHPUnit when installing Pest.'
            )
            ->addOption(
                'tools',
                't',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specific tools to install (phpstan, pest, pint, rector)',
                ['phpstan', 'pest', 'pint', 'rector']
            );

    }

    /** @codeCoverageIgnore  */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Installing Tools...</info>');

        try {
            $manager = new ComposerManager();
            $installer = new PHPPackageInstaller($manager);

            $selectedTools = $input->getOption('tools');
            $installAll = empty($selectedTools);

            $payload = new PackagePayload(
                packageInstaller: $installer,
                composerManager: $manager,
                input: $input,
                output: $output
            );

            $this->registerPhpStan($payload, $installAll, $selectedTools);
            $this->registerPest($payload, $installAll, $selectedTools);
            $this->registerPint($payload, $installAll, $selectedTools);
            $this->registerRector($payload, $installAll, $selectedTools);

            $installer->runPreInstallOperations($output);
            $uninstallSuccess = $installer->uninstall($output);
            $installSuccess = $installer->install($output);

            if ($installSuccess && $uninstallSuccess) {
                $installer->runPostInstallOperations($output);
                $output->writeln('<info>Tools installed successfully</info>');

                return Command::SUCCESS;
            }
            $output->writeln('<error>Failed to install tools</error>');

            return Command::FAILURE;

        } catch (InstallationException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        } catch (NoComposerException $e) {
            $output->writeln("<error>Composer error: {$e->getMessage()}</error>");

            return Command::FAILURE;
        } catch (Exception $e) {
            $output->writeln("<error>Unexpected error: {$e->getMessage()}</error>");

            return Command::FAILURE;
        }
    }

    private function registerPhpStan(PackagePayload $payload, bool $installAll, array $selectedTools): void
    {
        if ($installAll || in_array('phpstan', $selectedTools)) {
            $payload->packageInstaller->requirePackage(new PhpStanPackage(payload: $payload));
        }
    }

    private function registerPest(PackagePayload $payload, bool $installAll, array $selectedTools): void
    {
        if ($installAll || in_array('pest', $selectedTools)) {

            if (! $payload->input->getOption('no-phpunit')) {
                $payload->packageInstaller->removePackage(new PHPUnitPackage(payload: $payload));
            }

            $payload->packageInstaller->requirePackage(new PestPackage(payload: $payload));
        }
    }

    private function registerPint(PackagePayload $payload, bool $installAll, array $selectedTools): void
    {
        if ($installAll || in_array('pint', $selectedTools)) {

            $payload->packageInstaller->requirePackage(new PintPackage(payload: $payload));
        }
    }

    private function registerRector(PackagePayload $payload, bool $installAll, array $selectedTools): void
    {
        if ($installAll || in_array('rector', $selectedTools)) {
            $payload->packageInstaller->requirePackage(new RectorPackage(payload: $payload));
        }
    }
}
