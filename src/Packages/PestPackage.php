<?php

declare(strict_types=1);

namespace Pools\Packages;

use Pools\Concerns\Packages\InteractsWithStubs;
use Pools\Contracts\PHPPackage;
use Pools\Exceptions\NoComposerException;
use Pools\ValueObjects\PackagePayload;

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
