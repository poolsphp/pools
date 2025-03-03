<?php

declare(strict_types=1);

use Pools\Console\Commands\InstallCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

test('it sets all packages to true', function (): void {

    $app = new Application('Pools', '0.0.1');
    $app->add(new InstallCommand());

    $tester = new CommandTester($app->find('install'));

    $statusCode = $tester->execute(['--all' => true], ['interactive' => true]);

    [$all, $phpstan, $pest, $pint, $rector] = array_values(
        collect($tester->getInput()->getOptions())
            ->only('all', 'phpstan', 'pest', 'pint', 'rector')
            ->toArray());

    expect($statusCode)
        ->toBe(0)
        ->and($all && $phpstan && $pest && $pint && $rector)
        ->toBeTrue();

});

test('it prompts for the packages to be installed', function (): void {

    $app = new Application('Pools', '0.0.1');
    $app->add(new InstallCommand());

    $tester = new CommandTester($app->find('install'));

    $statusCode = $tester->execute([], ['interactive' => false]);

    expect($statusCode)->toBe(0);

});
