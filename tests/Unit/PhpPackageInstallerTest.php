<?php

declare(strict_types=1);

use PoolsPhp\Pools\ComposerManager;
use PoolsPhp\Pools\Packages\PestPackage;
use PoolsPhp\Pools\Packages\PHPUnitPackage;
use PoolsPhp\Pools\Packages\PintPackage;
use PoolsPhp\Pools\Packages\RectorPackage;
use PoolsPhp\Pools\PHPPackageInstaller;
use Symfony\Component\Console\Output\BufferedOutput;

test('install returns true if there are no packages to install', function (): void {

    $successInstall = $this->phpPackageInstaller->install(
        new BufferedOutput()
    );

    expect($successInstall)
        ->toBeTrue();

});

test('it installs the packages', function (): void {

    $this->phpPackageInstaller
        ->requirePackage(new RectorPackage($this->packagePayload))
        ->requirePackage(new PHPUnitPackage($this->packagePayload))
        ->requirePackage(new PintPackage($this->packagePayload));

    $successInstall = $this->phpPackageInstaller->install(
        new BufferedOutput()
    );

    expect($successInstall)->toBeTrue();

});

test('it uninstall a package', function (): void {

    $this->phpPackageInstaller
        ->requirePackage(
            new RectorPackage($this->packagePayload)
        );

    $this->phpPackageInstaller
        ->install(
            new BufferedOutput()
        );

    $this->phpPackageInstaller
        ->removePackage(
            new RectorPackage($this->packagePayload)
        );

    $successUninstall = $this->phpPackageInstaller->uninstall(
        new BufferedOutput()
    );

    expect($successUninstall)->toBeTrue();

});

test('uninstall returns true if there are no packages to be uninstalled', function (): void {

    $this->composerManager = new ComposerManager(
        $this->getFixturePath('composer.json')
    );
    $this->phpPackageInstaller = new PHPPackageInstaller($this->composerManager);

    $successUninstall = $this->phpPackageInstaller->uninstall(
        new BufferedOutput()
    );

    expect($successUninstall)
        ->toBeTrue();

});

test('uninstall returns true when there are no packages to be removed', function (): void {

    $successUninstall = $this->phpPackageInstaller->uninstall(
        new BufferedOutput()
    );

    expect($successUninstall)
        ->toBeTrue();

});

test('requirePackage adds to packages list', function (): void {
    // Arrange
    $rectorPackage = new RectorPackage($this->packagePayload);

    // Act
    $this->phpPackageInstaller->requirePackage($rectorPackage);

    // Assert
    expect($this->phpPackageInstaller->getPackagesToInstall())
        ->toHaveCount(1)
        ->toHaveKey('rector/rector');

});

test('removePackage adds package to removal list', function (): void {
    // Arrange
    $phpUnitPackage = new PHPUnitPackage($this->packagePayload);

    // Act
    $this->phpPackageInstaller->removePackage($phpUnitPackage);

    // Assert
    expect($this->phpPackageInstaller->getPackagesToRemove())
        ->toHaveCount(1)
        ->toHaveKey('phpunit/phpunit');

});

test('getBasePath returns the correct path', function (): void {
    // Arrange
    $expectedBasePath = dirname((string) $this->phpPackageInstaller->getVendorPath());

    // Assert
    expect($this->phpPackageInstaller->getBasePath())
        ->toBe($expectedBasePath);
});

test('getVendorPath returns the correct path', function (): void {
    try {
        // Arrange
        $expectedVendorPath = $this->composerManager->getComposer()->getConfig()->get('vendor-dir');

        // Assert
        expect($this->phpPackageInstaller->getVendorPath())
            ->toBe($expectedVendorPath);
    } catch (Throwable) {

    }

});

test('can chain package operations', function (): void {

    // Arrange
    $phpUnitPackage = new PHPUnitPackage($this->packagePayload);
    $rectorPackage = new RectorPackage($this->packagePayload);
    $pestPackage = new PestPackage($this->packagePayload);

    // Act
    $this->phpPackageInstaller
        ->removePackage($phpUnitPackage)
        ->requirePackage($rectorPackage)
        ->requirePackage($pestPackage);

    // Assert
    expect($this->phpPackageInstaller->getPackagesToRemove())
        ->toHaveCount(1)
        ->toHaveKey('phpunit/phpunit')
        ->and($this->phpPackageInstaller->getPackagesToInstall())
        ->toHaveCount(2);

});
