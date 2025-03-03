<?php

declare(strict_types=1);

namespace Pools\Packages;

use Pools\Concerns\Packages\InteractsWithStubs;
use Pools\Contracts\PHPPackage;
use Pools\ValueObjects\PackagePayload;

final class PHPUnitPackage implements PHPPackage
{
    use InteractsWithStubs;

    public string $name = 'PHPUnit';

    public string $package = 'phpunit/phpunit';

    public string $website = 'https://phpunit.de/';

    public string $github = 'https://github.com/sebastianbergmann/phpunit/';

    public function __construct(
        private readonly PackagePayload $payload,
    ) {}

    /**
     * @codeCoverageIgnore
     */
    public function beforeInstall(): void
    {
        // TODO: Implement beforeInstall() method.
    }

    /**
     * @codeCoverageIgnore
     */
    public function afterInstall(): void
    {
        $this->configure();
    }

    /**
     * @codeCoverageIgnore
     */
    public function configure(): void {}
}
