<?php

declare(strict_types=1);

namespace PoolsPhp\Pools\Concerns\Packages;

trait InteractsWithStubs
{
    protected function copyStubToRoot(string $stubName, string $targetName): void
    {
        copy(
            $this->payload->packageInstaller->getBasePath()."/stubs/{$stubName}",
            $this->payload->packageInstaller->getBasePath()."/{$targetName}"
        );
    }
}
