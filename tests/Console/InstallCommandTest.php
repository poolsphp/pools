<?php

declare(strict_types=1);

use Pools\Console\Commands\InstallCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Illuminate\Filesystem\Filesystem;

test('it installs larastan', function (): void {

    // Arrange
    $app = new Application('Pools', '0.0.1');
    $app->add(new InstallCommand());

    $tester = new CommandTester($app->find('install'));

    file_put_contents(
        $this->outputDirectory('temp/composer.json'),
        file_get_contents($this->fixturePath('composer-with-laravel.json'))
    );

    // Act
    $tester->execute([
        '--all' => false,
        '--phpstan' => true,
        '--type-check-level' => '5',
        '--overwrite-all' => true,
        '--larastan' => true,
    ],
        ['interactive' => true]);

    // Assert

    if(PHP_OS_FAMILY === 'Windows') {
        expect(file_exists($this->outputDirectory('temp/vendor/bin/phpstan.bat')))
            ->toBeTrue();
    } else {
        expect(file_exists($this->outputDirectory('temp/vendor/bin/phpstan')))
            ->toBeTrue();
    }


});

test('it overwrites installation', function (): void {
    // Arrange
    $app = new Application('Pools', '0.0.1');
    $app->add(new InstallCommand());

    $tester = new CommandTester($app->find('install'));

    $tester->execute([
        '--all' => true,
        '--type-check-level' => '5',
        '--overwrite-all' => true,
    ],
        ['interactive' => true]);

    // Act
    $tester->execute([
        '--pest' => true,
        '--pint' => true,
        '--rector' => true,
        '--overwrite-all' => 'false',
        '--overwrite-pest' => true,
    ],
        ['interactive' => true]);

    // Assert
    expect(file_exists($this->outputDirectory('temp/phpunit.xml')))
        ->toBeTrue()
        ->and(file_exists($this->outputDirectory('temp/pint.json')))
        ->toBeTrue()
        ->and(file_exists($this->outputDirectory('temp/rector.php')))
        ->toBeTrue();

});

test('install phpstan and overwrites config file', function (): void {

    // Arrange
    $app = new Application('Pools', '0.0.1');
    $app->add(new InstallCommand());

    $tester = new CommandTester($app->find('install'));

    file_put_contents(
        $this->outputDirectory('temp/phpstan.neon'),
        'foo'
    );

    // Act
    $tester->execute([
        '--phpstan' => true,
        '--overwrite-all' => 'false',
        '--type-check-level' => '5',
        '--overwrite-phpstan' => true,
    ],
        ['interactive' => true]);

    expect(file_exists($this->outputDirectory('temp/phpstan.neon')))
        ->toBeTrue()
        ->and(file_get_contents(__DIR__.'/../../stubs/phpstan/phpstan.neon'))
        ->toBe(file_get_contents($this->outputDirectory('temp/phpstan.neon')));

});

test('install all packages', function (): void {

    $app = new Application('Pools', '0.0.1');
    $app->add(new InstallCommand());

    $tester = new CommandTester($app->find('install'));

    $statusCode = $tester->execute([
        '--all' => true,
        '--type-check-level' => '5',
        '--overwrite-all' => true,
    ],
        ['interactive' => true]);

    [$all, $phpstan, $pest, $pint, $rector] = array_values(
        collect($tester->getInput()->getOptions())
            ->only('all', 'phpstan', 'pest', 'pint', 'rector')
            ->toArray());

    expect($statusCode)
        ->toBe(0)
        ->and($all && $phpstan && $pest && $pint && $rector)
        ->toBeTrue()
        ->and(file_exists($this->outputDirectory('temp/rector.php')))
        ->toBeTrue()
        ->and(file_exists($this->outputDirectory('temp/pint.json')))
        ->toBeTrue()
        ->and(file_exists($this->outputDirectory('temp/phpstan.neon')))
        ->toBeTrue()
        ->and(file_exists($this->outputDirectory('temp/phpunit.xml')))
        ->toBeTrue();

});

test('it prompts for the packages to be installed', function (): void {

    $app = new Application('Pools', '0.0.1');
    $app->add(new InstallCommand());

    $tester = new CommandTester($app->find('install'));

    $statusCode = $tester->execute([], ['interactive' => false]);

    expect($statusCode)->toBe(0);

});
