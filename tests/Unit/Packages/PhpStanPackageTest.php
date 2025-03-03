<?php

declare(strict_types=1);

use Pools\Packages\PHPUnitPackage;
use Symfony\Component\Console\Output\BufferedOutput;

test('packages can be configured', function (): void {

    // Arrange

    $package = new PHPUnitPackage($this->packagePayload);
    $this->phpPackageInstaller->requirePackage(
        $package
    );

    $mock = Mockery::mock($package);
    $mock->shouldNotReceive('configure')
        ->andReturnNull();

    $this->phpPackageInstaller->install(
        new BufferedOutput()
    );

});

afterEach(function (): void {
    Mockery::close();
});
