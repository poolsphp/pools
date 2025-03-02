<?php

declare(strict_types=1);

namespace PoolsPhp\Pools\Packages;

use PoolsPhp\Pools\Concerns\Packages\InteractsWithStubs;
use PoolsPhp\Pools\Contracts\PHPPackage;
use PoolsPhp\Pools\ValueObjects\PackagePayload;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_string;
use function str_replace;

final class PhpStanPackage implements PHPPackage
{
    use InteractsWithStubs;

    public string $name = 'PHPStan';

    public string $package = 'phpstan/phpstan';

    public string $website = 'https://phpstan.org/';

    public string $github = 'https://github.com/phpstan/phpstan';

    public function __construct(
        private readonly PackagePayload $payload,
    ) {}

    public function configure(): void
    {
        $helper = new QuestionHelper();
        $basePath = $this->payload->packageInstaller->getBasePath();

        if (file_exists("{$basePath}/phpstan.neon")) {
            $doYouWantToOverwrite = new Question(
                'phpstan.neon already exists. Do you want to overwrite it? (yes/no): ',
                'no'
            );

            $overwrite = $helper->ask($this->payload->input, $this->payload->output, $doYouWantToOverwrite);
            if ($overwrite === 'no') {
                return;
            }
        }

        $question = new Question(
            'What level of PHPStan would you like to use? (0-9, default: 5): ',
            '5'
        );

        $level = $helper->ask($this->payload->input, $this->payload->output, $question);

        $level = (int) $level;

        $fileOutput = file_get_contents("{$basePath}/stubs/phpstan.neon");
        assert(is_string($fileOutput));
        $fileOutput = str_replace('level: 5', "level: {$level}", $fileOutput);
        file_put_contents("{$basePath}/phpstan.neon", $fileOutput);
    }

    public function beforeInstall(): void
    {
        // TODO: Implement beforeInstall() method.
    }

    public function afterInstall(): void
    {
        $this->configure();
    }
}
