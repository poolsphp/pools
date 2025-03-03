<?php

use Pools\Console\Commands\InstallCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

test('it prompts for the packages to be installed', function (): void {

    $app = new Application('Pools', '0.0.1');
    $app->add(new InstallCommand());

    $tester = new CommandTester($app->find('install'));

    $statusCode = $tester->execute([
        '--all' => true,
        '--phpstan' => false,
        '--pest' => false,
        '--pint' => false,
        '--rector' => false,
    ], ['interactive' => false]);


    expect($statusCode)->toBe(0);

});
