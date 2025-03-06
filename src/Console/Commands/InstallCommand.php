<?php

declare(strict_types=1);

namespace Pools\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\ProcessUtils;
use Pools\Concerns\Console\InteractsWithPrompts;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

use function array_key_exists;
use function dirname;
use function file_exists;
use function file_get_contents;
use function function_exists;
use function getcwd;
use function implode;
use function json_decode;
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
            ->addOption('larastan', null, InputOption::VALUE_NONE, 'Installs Larastan')
            ->addOption('type-check-level', null, InputOption::VALUE_NONE, 'Sets the PHPStan level')
            ->addOption('overwrite-phpstan', null, InputOption::VALUE_NONE, 'Overwrites the existing PHPStan|Larastan configuration')
            ->addOption('pest', null, InputOption::VALUE_NONE, 'Installs Pest')
            ->addOption('overwrite-pest', null, InputOption::VALUE_NONE, 'Overwrites existing Pest configuration')
            ->addOption('pint', null, InputOption::VALUE_NONE, 'Installs Pint')
            ->addOption('overwrite-pint', null, InputOption::VALUE_NONE, 'Overwrites existing pint configuration')
            ->addOption('rector', null, InputOption::VALUE_NONE, 'Installs Rector')
            ->addOption('overwrite-rector', null, InputOption::VALUE_NONE, 'Overwrites existing Rector configuration');

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

        if ($input->getOption('phpstan') && $this->typeCheckConfigExists($directory) && ! $input->getOption('overwrite-phpstan')) {
            $input->setOption(
                'overwrite-phpstan',
                confirm('Would you like to overwrite the existing PHPStan|Larastan configuration?')
            );
        }

        if ($input->getOption('pint') && $this->pintConfigExists($directory) && ! $input->getOption('overwrite-pint')) {
            $input->setOption(
                'overwrite-pint',
                confirm('Would you like to overwrite the existing Pint configuration?')
            );
        }

        if ($input->getOption('rector') && $this->rectorConfigExists($directory) && ! $input->getOption('overwrite-rector')) {
            $input->setOption(
                'overwrite-rector',
                confirm('Would you like to overwrite the existing Rector configuration?')
            );
        }

        if ($input->getOption('pest') && $this->pestIsInstalled($directory) && ! $input->getOption('overwrite-pest')) {
            $input->setOption(
                'overwrite-pest',
                confirm('Would you like to overwrite the existing Pest configuration?')
            );
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

        if ($input->getOption('pint')) {
            $this->installPint($directory, $input, $output);
        }

        if ($input->getOption('rector')) {
            $this->installRector($directory, $input, $output);
        }

        if ($input->getOption('pest')) {
            $this->installPest($directory, $input, $output);
        }

        return Command::SUCCESS;
    }

    private function init(string $directory): void
    {
        $this->composer = new Composer(new Filesystem(), $directory);
        $this->isLaravelApp = $this->isLaravelApp($directory);
    }

    private function findComposer(): string
    {
        return implode(' ', $this->composer->findComposer());
    }

    private function installPest(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $composerBinary = $this->findComposer();

        $commands = [
            $composerBinary.' remove phpunit/phpunit --dev --no-update',
            $composerBinary.' require pestphp/pest pestphp/pest-plugin-faker pestphp/pest-plugin-watch  --with-all-dependencies --dev --no-update',
            $composerBinary.' update',
        ];

        if ($input->getOption('overwrite-pest') && $this->pestIsInstalled($directory)) {
            $fs = new Filesystem();
            $fs->delete($directory.'/phpunit.xml');
            $fs->delete($directory.'/tests/Pest.php');
            $fs->delete($directory.'/tests/TestCase.php');
            $fs->delete($directory.'/tests/Unit/ExampleTest.php');
            $fs->delete($directory.'/tests/Feature/ExampleTest.php');
            $commands[] = $this->phpBinary().' ./vendor/bin/pest --init';
        } elseif (! $this->pestIsInstalled($directory)) {
            $commands[] = $this->phpBinary().' ./vendor/bin/pest --init';
        }

        $this->runCommands($commands, $output, $directory);

    }

    private function installPint(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $composerBinary = $this->findComposer();

        $commands = [
            $composerBinary.' require --dev laravel/pint',
        ];

        $this->runCommands($commands, $output, $directory);

        if ($this->pintConfigExists($directory) && $input->getOption('overwrite-pint')) {
            $this->copyPintStubs($directory);
        } elseif (! $this->pintConfigExists($directory)) {
            $this->copyPintStubs($directory);
        }
    }

    private function installRector(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $composerBinary = $this->findComposer();

        $commands = [
            $composerBinary.' require rector/rector --no-update --dev',
        ];

        $this->runCommands($commands, $output, $directory);

        if ($this->rectorConfigExists($directory) && $input->getOption('overwrite-rector')) {
            $this->copyRectorStubs($directory);
        } elseif (! $this->rectorConfigExists($directory)) {
            $this->copyRectorStubs($directory);
        }
    }

    /** @codeCoverageIgnore  */
    private function phpBinary(): string
    {
        $phpBinary = function_exists('Illuminate\Support\php_binary')
            ? \Illuminate\Support\php_binary()
            : (new PhpExecutableFinder())->find(false);

        return $phpBinary !== false
            ? ProcessUtils::escapeArgument($phpBinary)
            : 'php';
    }

    private function pestIsInstalled(string $directory): bool
    {
        return file_exists($directory.'/vendor/bin/pest')
            && file_exists($directory.'/phpunit.xml')
            && file_exists($directory.'/tests/Pest.php');
    }

    private function rectorConfigExists(string $directory): bool
    {
        return file_exists($directory.'/rector.php');
    }

    private function copyRectorStubs(string $directory): void
    {
        $this->replaceFile(
            'rector/rector.php',
            $directory.'/rector.php'
        );
    }

    private function installPhpStan(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $composerBinary = $this->findComposer();

        $this->confirmLarastanInstall($input);

        $commands = match ($input->getOption('larastan')) {
            true => [
                $composerBinary.' require --dev larastan/larastan',
            ],
            default => [
                $composerBinary.' require --dev phpstan/phpstan',
                $composerBinary.' require --dev phpstan/phpstan-phpunit',
                $composerBinary.' require --dev phpstan/phpstan-deprecation-rules',
            ],
        };

        $this->runCommands($commands, $output, $directory);

        if ($this->typeCheckConfigExists($directory) && $input->getOption('overwrite-phpstan')) {
            $this->copyTypeCheckStubs($directory, $input->getOption('type-check-level'), $input->getOption('larastan'));
        } elseif (! $this->typeCheckConfigExists($directory)) {
            $this->copyTypeCheckStubs($directory, $input->getOption('type-check-level'), $input->getOption('larastan'));
        }

    }

    /** @codeCoverageIgnore  */
    private function confirmLarastanInstall(InputInterface $input): void
    {
        if ($this->isLaravelApp && ! $input->getOption('larastan')) {
            $input->setOption('larastan', confirm(
                label: 'Your project has Laravel installed. Would you like to install Larastan instead PHPStan?',
                hint: 'Larastan is a wrapper around PHPStan that provides a better experience for Laravel applications.'
            ));
        }
    }

    private function typeCheckConfigExists(string $directory): bool
    {
        return file_exists($directory.'/phpstan.neon') || file_exists($directory.'/phpstan.neon.dist');
    }

    private function pintConfigExists(string $directory): bool
    {
        return file_exists($directory.'/pint.json');
    }

    private function copyPintStubs(string $directory): void
    {
        $this->replaceFile(
            'pint/pint.json',
            $directory.'/pint.json'
        );

        if (! $this->isLaravelApp) {
            $this->replaceInFile(
                '"preset": "laravel"',
                '',
                $directory.'/pint.json'
            );
        }

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

    private function isLaravelApp(string $directory): bool
    {

        $composerFile = $directory.'/composer.json';

        if (! file_exists($composerFile)) {
            return false;
        }

        $composer = json_decode(file_get_contents($composerFile), true);

        return array_key_exists('laravel/framework', $composer['require'] ?? [])
            || array_key_exists('laravel/framework', $composer['require-dev'] ?? []);

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
