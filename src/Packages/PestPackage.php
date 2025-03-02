<?php

declare(strict_types=1);

namespace PoolsPhp\Pools\Packages;

use PoolsPhp\Pools\Concerns\Packages\InteractsWithStubs;
use PoolsPhp\Pools\Contracts\PHPPackage;
use PoolsPhp\Pools\Exceptions\NoComposerException;
use PoolsPhp\Pools\ValueObjects\PackagePayload;

final class PestPackage implements PHPPackage
{
    use InteractsWithStubs;

    public string $name = 'Pest';

    public string $package = 'pestphp/pest';

    public string $website = 'https://pestphp.com/';

    public string $github = 'https://github.com/pestphp/pest';

    public function __construct(
        private readonly PackagePayload $payload,
    ) {}

    /**
     * @throws NoComposerException
     */
    public function beforeInstall(): void
    {
        $this->payload->output->writeln('<info>Removing PHPUnit before installing Pest...</info>');
        $this->payload->packageInstaller->removePackage(new PHPUnitPackage($this->payload));
    }

    public function afterInstall(): void
    {
        $this->configure();
    }

    public function configure(): void
    {
        // TODO: Implement configure() method.
    }
}
