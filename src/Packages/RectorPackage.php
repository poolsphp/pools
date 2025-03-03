<?php

declare(strict_types=1);

namespace Pools\Packages;

use Pools\Concerns\Packages\InteractsWithStubs;
use Pools\Contracts\PHPPackage;
use Pools\ValueObjects\PackagePayload;

final class RectorPackage implements PHPPackage
{
    use InteractsWithStubs;

    public string $name = 'Rector';

    public string $package = 'rector/rector';

    public string $website = 'https://getrector.org/';

    public string $github = 'https://github.com/rectorphp/rector';

    public function __construct(
        private readonly PackagePayload $payload
    ) {}

    public function configure(): void
    {
        $this->payload->output->writeln('<info>Configuring Rector...</info>');
        $this->copyStubToRoot('rector.php', 'rector.php');
    }

    public function beforeInstall(): void
    {
        // TODO: Implement beforeInstall() method.
    }

    public function afterInstall(): void
    {
        // TODO: Implement afterInstall() method.
    }
}
