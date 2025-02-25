<?php

declare(strict_types=1);


use PoolsPhp\Pools\Commands\InstallRectorCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

test('it installs rector through the command', function (): void {
    // Create a new application
    $application = new Application();

    // Add our command to the application
    $application->add(new InstallRectorCommand());

    // Get the command
    $command = $application->find('install:rector');

    // Create a command tester
    $commandTester = new CommandTester($command);

    // Execute the command
    $commandTester->execute([]);

    // Assert that the command was successful
    expect($commandTester->getStatusCode())
        ->toBe(0);
});
