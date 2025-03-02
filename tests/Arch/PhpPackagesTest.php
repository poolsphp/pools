<?php

declare(strict_types=1);

use PoolsPhp\Pools\Concerns\Packages\InteractsWithStubs;
use PoolsPhp\Pools\Contracts\PHPPackage;

arch('packages')
    ->expect('PoolsPhp\Pools\Packages')
    ->toHaveSuffix('Package')
    ->toHaveConstructor()
    ->toUseTrait(InteractsWithStubs::class)
    ->toBeClasses()
    ->toBeFinal()
    ->toUseStrictTypes()
    ->toImplement(PHPPackage::class);
