<?php

use PoolsPhp\Pools\ComposerReader;

test('it reads the contents of composer', function (): void {

    $composerReader = new ComposerReader;
    $composer = $composerReader->getComposerJson();

    expect($composer)->toBeArray();
    expect($composer['name'])->toBe('pools-php/pools');
    expect($composer['description'])->toBe('A simple and fast implementation of a thread pool in PHP');
    expect($composer['license'])->toBe('MIT');
    expect($composer['authors'])->toBeArray();
    expect($composer['authors'][0]['name'])->toBe('Nuno Maduro');

});
