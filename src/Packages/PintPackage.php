<?php

declare(strict_types=1);

namespace Pools\Packages;

use Pools\Concerns\Packages\InteractsWithStubs;
use Pools\Contracts\PHPPackage;
use Pools\ValueObjects\PackagePayload;

final class PintPackage implements PHPPackage
{
    use InteractsWithStubs;

    public string $name = 'Laravel Pint';

    public string $package = 'laravel/pint';

    public string $website = 'https://laravel.com/docs/12.x/pint';

    public string $github = 'https://github.com/laravel/pint';

    public function __construct(
        private readonly PackagePayload $payload
    ) {}

    public function configure(): void
    {
        $this->payload->output->writeln('<info>Configuring Laravel Pint...</info>');
        $this->copyStubToRoot('pint.json', 'pint.json');
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
