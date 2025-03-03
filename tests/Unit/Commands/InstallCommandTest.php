<?php

declare(strict_types=1);

use Pools\Console\Commands\DefaultCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

test('it installs phpstan successfully', function (): void {
    // Create a new application
    $application = new Application();

    // Add our command to the application
    $application->add(new DefaultCommand());

    // Get the command
    $command = $application->find('default');

    // Create a command tester
    $commandTester = new CommandTester($command);

    $commandTester->setInputs([
        'overwrite' => 'yes',
        'level' => '9',
    ]);

    // Execute the command
    $commandTester->execute([
        '--tools' => ['phpstan'],
    ]);

    // Assert that the command was successful
    expect($commandTester->getStatusCode())->toBe(0);
});

test('it installs packages successfully', function (): void {
    // Create a new application
    $application = new Application();

    // Add our command to the application
    $application->add(new DefaultCommand());

    // Get the command
    $command = $application->find('default');

    // Create a command tester
    $commandTester = new CommandTester($command);
    $commandTester->setInputs([
        'overwrite' => 'no',
        'level' => '5',
    ]);

    // Execute the command
    $commandTester->execute([]);

    // Assert that the command was successful
    expect($commandTester->getStatusCode())->toBe(0);
});
