<?php

declare(strict_types=1);

namespace Pools\Console\Commands;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Pools\Concerns\Console\InteractsWithPrompts;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;
use function getcwd;
use function implode;
use function Laravel\Prompts\multiselect;

#[AsCommand(name: 'install')]
final class InstallCommand extends Command
{
    use InteractsWithPrompts;

    private Composer $composer;

    protected function configure(): void
    {
        $this->setNAme('install')
            ->setDescription('Installs and configures Modern PHP tools for your project')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Installs all packages')
            ->addOption('phpstan', null, InputOption::VALUE_NONE, 'Installs PHPStan')
            ->addOption('pest', null, InputOption::VALUE_NONE, 'Installs Pest')
            ->addOption('pint', null, InputOption::VALUE_NONE, 'Installs Pint')
            ->addOption('rector', null, InputOption::VALUE_NONE, 'Installs Rector');
    }

    /** @codeCoverageIgnore  */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {

        parent::interact($input, $output);

        $this->configurePrompts($input, $output);

        $this->displayPoolsLogo($output);
        if (! $input->getOption('all') && ! $input->getOption('phpstan') && ! $input->getOption('pest') && ! $input->getOption('pint') && ! $input->getOption('rector')) {

            $packages = multiselect(
                label: 'Which packages would you like to install?',
                options: [
                    'phpstan' => 'PHPStan',
                    'pest' => 'Pest',
                    'pint' => 'Pint',
                    'rector' => 'Rector',
                ],
            );

            foreach ($packages as $package) {
                $input->setOption($package, true);
            }

        } elseif ($input->getOption('all')) {
            $input->setOption('phpstan', true);
            $input->setOption('pest', true);
            $input->setOption('pint', true);
            $input->setOption('rector', true);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $directory = getcwd();

        $this->composer = new Composer(new Filesystem(), $directory);

        if($input->getOption('phpstan')) {
            $this->installPhpStan($directory, $input, $output);
        }

        return Command::SUCCESS;
    }

    private function installPhpStan(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $composerBinary = $this->findComposer();

        $commands = [
            $composerBinary.' require --dev phpstan/phpstan',
            $composerBinary.' require --dev phpstan/phpstan-phpunit',
            $composerBinary.' require --dev phpstan/phpstan-deprecation-rules'
        ];

        $process = Process::fromShellCommandline(
            command: implode(' &&', $commands),
            cwd: $directory,
            env: [],
            timeout: null
        );

        $process->run(function ($type, $buffer) use ($output) {
            $output->write('    '.$buffer);
        });
    }

    protected function findComposer(): string
    {
        return implode(' ', $this->composer->findComposer());
    }





    /** @codeCoverageIgnore  */
    private function displayPoolsLogo(OutputInterface $output): void
    {
        $logo = <<<EOT
    <fg=blue>
     ___   ___    ___   _      ___
    | _ \ / _ \  / _ \ | |    / __|
    |  _/| (_) || (_) || |__  \__ \\
    |_|   \___/  \___/ |____| |___/
    </>
EOT;

        $output->write($logo.PHP_EOL);
    }
}
