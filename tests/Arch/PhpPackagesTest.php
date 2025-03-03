<?php

declare(strict_types=1);

use Pools\Concerns\Packages\InteractsWithStubs;
use Pools\Contracts\PHPPackage;

arch('packages')
    ->expect('Pools\Packages')
    ->toHaveSuffix('Package')
    ->toHaveConstructor()
    ->toUseTrait(InteractsWithStubs::class)
    ->toBeClasses()
    ->toBeFinal()
    ->toUseStrictTypes()
    ->toImplement(PHPPackage::class);
