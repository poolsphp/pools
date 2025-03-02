<?php

declare(strict_types=1);

use PoolsPhp\Pools\Packages\RectorPackage;

beforeEach(function (): void {

    $this->package = new RectorPackage($this->packagePayload);

});

afterEach(function (): void {
    // Clean up any test files
    $configPath = $this->getTempFileSystemPath('rector.php');
    if (file_exists($configPath)) {
        unlink($configPath);
    }

});

test('rector package has correct properties', function (): void {
    expect($this->package->name)
        ->toBe('Rector')
        ->and($this->package->package)
        ->toBe('rector/rector')
        ->and($this->package->website)
        ->toBe('https://getrector.org/')
        ->and($this->package->github)
        ->toBe('https://github.com/rectorphp/rector');
});

test('configure copies rector config file to project root', function (): void {

    // Act
    $this->package->configure();

    $configPath = $this->getTempFileSystemPath('rector.php');
    $stubContent = file_get_contents($this->getTempFileSystemPath('stubs/rector.php'));
    expect(file_exists($configPath))
        ->toBeTrue()
        ->and(file_get_contents($configPath))
        ->toBe($stubContent);

});
