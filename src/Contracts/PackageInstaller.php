<?php

declare(strict_types=1);

namespace PoolsPhp\Pools\Contracts;

/**
 * @internal description
 */
interface PackageInstaller
{
    public function install(): bool;
}
