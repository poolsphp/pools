<?php

declare(strict_types=1);

test('it installs rector', function (): void {
    $rectorInstaller = new PoolsPhp\Pools\RectorInstaller;
    $output = $rectorInstaller->install();

    expect($output)
        ->dd($output)
        ->toBeString()
        ->and($output)
        ->toContain('rector/rector');
});
