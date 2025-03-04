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

use function dirname;
use function file_exists;
use function file_get_contents;
use function getcwd;
use function implode;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\text;
use function str_replace;

#[AsCommand(name: 'install')]
final class InstallCommand extends Command
{
    use InteractsWithPrompts;

    private Composer $composer;

    private bool $isLaravelApp;

    protected function configure(): void
    {
        $this->setNAme('install')
            ->setDescription('Installs and configures Modern PHP tools for your project')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Installs all packages')
            ->addOption('phpstan', null, InputOption::VALUE_NONE, 'Installs PHPStan')
            ->addOption('type-check-level', null, InputOption::VALUE_NONE, 'Sets the PHPStan level')
            ->addOption('overwrite-phpstan-config', null, InputOption::VALUE_NONE, 'Overwrites the existing PHPStan|Larastan configuration?')
            ->addOption('pest', null, InputOption::VALUE_NONE, 'Installs Pest')
            ->addOption('pint', null, InputOption::VALUE_NONE, 'Installs Pint')
            ->addOption('rector', null, InputOption::VALUE_NONE, 'Installs Rector');

    }

    /** @codeCoverageIgnore  */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {

        parent::interact($input, $output);

        $directory = getcwd();

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

        if ($input->getOption('phpstan') && ! $input->getOption('type-check-level')) {
            $input->setOption('type-check-level', text('What level would you like to set PHPStan to (0-10)?', '5', '5'));
        }

        if ($input->getOption('phpstan') && $this->typeCheckConfigExists($directory) && ! $input->getOption('overwrite-phpstan-config')) {
            $input->setOption('overwrite-phpstan-config', confirm('Would you like to overwrite the existing PHPStan|Larastan configuration?'));
        }

        $this->init($directory);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $directory = getcwd();

        if (! $input->getOption('no-interaction')) {
            $this->init($directory);
        }

        if ($input->getOption('phpstan')) {
            $this->installPhpStan($directory, $input, $output);
        }

        return Command::SUCCESS;
    }

    private function init(string $directory): void
    {
        $this->composer = new Composer(new Filesystem(), $directory);
        $this->isLaravelApp = $this->isLaravelApp();
    }

    private function findComposer(): string
    {
        return implode(' ', $this->composer->findComposer());
    }

    private function installPhpStan(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $composerBinary = $this->findComposer();

        $installLarastan = false;
        if ($this->isLaravelApp) {
            $installLarastan = confirm(
                label: 'Your project has Laravel installed. Would you like to install Larastan instead PHPStan?',
                hint: 'Larastan is a wrapper around PHPStan that provides a better experience for Laravel applications.'
            );
        }

        $commands = match ($installLarastan) {
            true => [
                $composerBinary.' require --dev nunomaduro/larastan',
            ],
            default => [
                $composerBinary.' require --dev phpstan/phpstan',
                $composerBinary.' require --dev phpstan/phpstan-phpunit',
                $composerBinary.' require --dev phpstan/phpstan-deprecation-rules',
            ],
        };

        $this->runCommands($commands, $output, $directory);

        if ($this->typeCheckConfigExists($directory) && $input->getOption('overwrite-phpstan-config')) {
            $this->copyTypeCheckStubs($directory, $input->getOption('type-check-level'), $installLarastan);
        } elseif (! $this->typeCheckConfigExists($directory)) {
            $this->copyTypeCheckStubs($directory, $input->getOption('type-check-level'), $installLarastan);
        }

    }

    private function typeCheckConfigExists(string $directory): bool
    {
        return file_exists($directory.'/phpstan.neon') || file_exists($directory.'/phpstan.neon.dist');
    }

    private function copyTypeCheckStubs(string $directory, string $typeCheckLevel = '5', bool $configureLarastan = false): void
    {
        if ($configureLarastan) {
            $this->replaceFile(
                'larastan/phpstan.neon',
                $directory.'/phpstan.neon'
            );
        } else {
            $this->replaceFile(
                'phpstan/phpstan.neon',
                $directory.'/phpstan.neon',
            );
        }

        $this->replaceInFile(
            'level: 5',
            "level: {$typeCheckLevel}",
            $directory.'/phpstan.neon'
        );
    }

    private function replaceFile(string $replace, string $file): void
    {
        $stubs = dirname(__DIR__).'/../../stubs';

        file_put_contents(
            $file,
            file_get_contents($stubs.'/'.$replace)
        );
    }

    private function replaceInFile(string|array $search, string|array $replace, string $file): void
    {
        file_put_contents(
            $file,
            str_replace($search, $replace, file_get_contents($file))
        );
    }

    private function isLaravelApp(): bool
    {
        return $this->composer->hasPackage('laravel/framework');
    }

    private function runCommands(array $commands, OutputInterface $output, ?string $workingDirectory = null, array $env = []): Process
    {
        $process = Process::fromShellCommandline(
            command: implode(' &&', $commands),
            cwd: $workingDirectory ?? getcwd(),
            env: $env,
            timeout: null
        );

        $process->run(function ($type, string $buffer) use ($output): void {
            $output->write('    '.$buffer);
        });

        return $process;
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
