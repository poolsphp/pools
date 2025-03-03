<?php

namespace Pools\Console\Commands;


use Pools\Concerns\Console\InteractsWithPrompts;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function dump;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

#[AsCommand(name: 'install')]
class InstallCommand extends Command
{

    use InteractsWithPrompts;

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

    protected function interact(InputInterface $input, OutputInterface $output): void
    {

        parent::interact($input, $output);

        $this->configurePrompts($input, $output);

        $this->displayPoolsLogo($output);

        $packages = [];
        if (!$input->getOption('all') && !$input->getOption('phpstan') && !$input->getOption('pest') && !$input->getOption('pint') && !$input->getOption('rector') ) {

            $packages = multiselect(
                label: 'Which packages would you like to install?',
                options: [
                    'phpstan' => 'PHPStan',
                    'pest' => 'Pest',
                    'pint' => 'Pint',
                    'rector' => 'Rector',
                ],
                default: []
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

        dump($input->getOptions());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        return Command::SUCCESS;
    }

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

        $output->write($logo . PHP_EOL);
    }


}
