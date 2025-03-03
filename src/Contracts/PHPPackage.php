<?php

declare(strict_types=1);

namespace Pools\Contracts;

/**
 * @property-read string $name      Package display name
 * @property-read string $package   Composer package name
 * @property-read string $website   Package website
 * @property-read string $github    Package GitHub URL
 */
interface PHPPackage
{
    /**
     * Operations to perform before installing the package
     */
    public function beforeInstall(): void;

    /**
     * Operations to perform after installing the package
     */
    public function afterInstall(): void;

    /**
     * Configure the package
     */
    public function configure(): void;
}
