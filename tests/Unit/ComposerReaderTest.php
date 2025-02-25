<?php

use PoolsPhp\Pools\ComposerReader;

test('it reads the contents of composer', function (): void {

    $composerReader = new ComposerReader;
    $composer = $composerReader->getComposerJson();

    expect($composer)->toBeArray()
        ->and($composer['name'])->toBe('pools-php/pools')
        ->and($composer['description'])->toBe('A simple and fast implementation of a thread pool in PHP')
        ->and($composer['license'])->toBe('MIT')
        ->and($composer['authors'])->toBeArray()
        ->and($composer['authors'][0]['name'])->toBe('Francisco Barrento');

});
