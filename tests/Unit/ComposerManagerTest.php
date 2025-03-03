<?php

declare(strict_types=1);

use Composer\Composer;
use Pools\ComposerManager;
use Pools\Exceptions\NoComposerException;

beforeEach(function (): void {
    $this->fixturesPath = __DIR__.'/../fixtures';
    $this->manager = new ComposerManager($this->fixturesPath.'/composer.json');
});

test('getComposer returns valid composer instance when given a composer path', function (): void {

    $composer = $this->manager->getComposer($this->getFixturePath('composer.json'));

    expect($composer)->toBeInstanceOf(Composer::class);

});

test('getComposer returns valid composer instance', function (): void {
    $composer = $this->manager->getComposer();

    expect($composer)->toBeInstanceOf(Composer::class);
});

test('getDevDependencies returns correct dependencies', function (): void {
    $devDependencies = $this->manager->getDevDependencies(
        $this->fixturesPath.'/composer-with-dev-dependencies.json'
    );

    expect($devDependencies)
        ->toBeArray()
        ->and(array_keys($devDependencies))
        ->toBe([
            'laravel/pint',
            'phpunit/phpunit',
            'pestphp/pest',
            'pestphp/pest-plugin-type-coverage',
            'phpstan/phpstan',
            'rector/rector',
        ]);
});

test('constructor throws exception for non-existent composer file', function (): void {
    expect(fn (): ComposerManager => new ComposerManager('/path/to/nonexistent/composer.json'))
        ->toThrow(NoComposerException::class);
});
